# 🌐 GUIAR - Sistema Inteligente com IoT

## Contato flutuante

Na página inicial, o botão **Fale com o GUIAR** abre um formulário que envia mensagens para `projGuiar@gmail.com`. Execute o projeto pelo Apache/PHP (por exemplo, `http://localhost/GUIAR/index.html`).

O envio usa PHPMailer, já instalado no projeto, com a mesma configuração SMTP do cadastro e da recuperação de senha. Essa configuração está centralizada em `mvc/Services/EmailService.php` e é reutilizada pelo `EmpresaController` e por `enviarContato.php`; não é necessário configurar novas variáveis de ambiente para o contato. O e-mail informado no formulário é usado como endereço de resposta.

## Testes automatizados

O workflow do GitHub Actions executa os testes ao enviar um `push` ou abrir um `pull request` para `master`. Também pode ser iniciado manualmente em **Actions > Testes do GUIAR > Run workflow**.

Quando os testes de frontend e backend passam, o workflow gera um pacote `guiar-build.zip` e publica-o como artefato para download na página da execução do GitHub Actions.

Para executar os testes localmente, instale Node.js 22, PHP 8.2 com as extensões PDO SQLite e mbstring, e Composer. Depois rode:

```sh
npm ci
npx playwright install chromium
composer install
php tests/backend/model_smoke.php
php tests/backend/eta_regressor_test.php
php tests/backend/previsao_fila_test.php
php tests/backend/inteligencia_test.php
```

Para o teste do navegador, inicie o servidor PHP em um terminal:

```sh
php -S 127.0.0.1:8080 -t .
```

Em outro terminal, execute:

```sh
npm run test:frontend
```

## Previsão de tempo de entrega (machine learning)

No mapa do entregador, ao tocar em **Começar Percurso**, o sistema traça a rota até a entrega mais próxima e mostra um painel com tempo restante, distância e horário previsto de chegada, atualizado enquanto o entregador se move.

**Como a previsão é feita**

1. O [OSRM](https://project-osrm.org/) calcula a rota e o tempo pelas ruas (sem trânsito).
2. Um modelo treinado com as entregas reais do GUIAR corrige esse tempo. Ele aprende quanto o tempo real costuma desviar do OSRM por faixa de horário, dia da semana, bairro, distância e entregador.
   - Técnica: regressão linear com regularização L2 (ridge) sobre `ln(tempo real ÷ tempo OSRM)`, com o λ escolhido por validação cruzada. Código em `mvc/Services/EtaRegressor.php`, PHP puro, sem dependências.
   - O modelo é retreinado automaticamente a cada 5 entregas concluídas.
   - Ele só é usado depois de 10 entregas válidas **e** se, nas entregas mais recentes (que ele não viu no treino), errar menos que o OSRM puro. Antes disso, o painel mostra o tempo do OSRM.

**Coleta dos dados**: cada trajeto é gravado na tabela `entrega_viagem`, com início, previsão, chegada detectada pelo GPS (raio de 40 m) e conclusão. Trajetos não confiáveis são descartados com o motivo registrado: duração menor que 30 s, conclusão a mais de 300 m do destino sem chegada detectada, tempo fora de 0,25×–6× o do OSRM, ou rota abandonada.

**Instalação em um banco existente** (local e serv00), uma única vez:

```sh
mysql -u USUARIO -p guiartcc < database/migrations/2026_11_17_estimativa_tempo_ml.sql
```

**Acompanhar o modelo**

```sh
php scripts/treinar_modelo_eta.php --status   # coleta, versão ativa, erro do OSRM × modelo e peso de cada atributo
php scripts/treinar_modelo_eta.php            # força um novo treino
```

Os mesmos dados estão em `routes.php?action=apiEtaStatus` (GET) e `routes.php?action=apiEtaTreinar` (POST), com sessão de administrador.

## Rastreio do cliente, rota otimizada e inteligência

Tudo sem custo: OSRM público, Leaflet, links `wa.me` do WhatsApp (sem a API paga) e Wake Lock do navegador.

- **Link de rastreio para o cliente:** em **Pedidos**, o botão **📍 Rastrear** abre o link público do pedido (`routes.php?action=rastreio&t=...`), com as opções de copiar ou enviar pelo WhatsApp. O número usado é o campo opcional *WhatsApp do cliente*; sem ele, o WhatsApp abre para você escolher o contato.
  - A página do cliente mostra as etapas (recebido → saiu → a caminho → entregue), o horário previsto e, quando o entregador está vindo para aquele endereço, a moto e a rota no mapa. Ela atualiza a cada 10 s.
  - O link precisa ser gerado com o painel aberto pelo endereço de rede (ex.: `https://10.0.3.168:8000`). Um link com `localhost` não abre no celular do cliente, e o modal avisa quando isso acontece.
- **Rota otimizada:** ao tocar em **Começar Percurso**, o entregador recebe a melhor ordem das paradas (serviço `trip` do OSRM). Cada trecho é ajustado pelo modelo, e o painel mostra a lista *Próximas paradas* com o horário de cada uma. Os marcadores do mapa ficam numerados na ordem de visita.
- **Previsão ao vivo:** o celular do entregador envia o tempo restante a cada recálculo (`apiEtaProgresso`). O link do cliente e o mapa do gestor usam esse mesmo valor. As paradas seguintes somam a chegada atual, o **tempo médio no endereço** (aprendido do histórico; 2 min até haver dados) e os trechos planejados (`mvc/Services/PrevisaoFila.php`).
- **Mapa do gestor:** cada entregador mostra a próxima entrega com o horário, e cada parada mostra a previsão na ordem da rota.
- **Tela sempre ligada:** durante o percurso, a tela do entregador fica acesa (Wake Lock), para o GPS e a previsão não pararem. Isso exige HTTPS ou `localhost`.
- **Inteligência:** no menu do gestor, mostra a pontualidade da previsão, o erro do modelo contra o OSRM a cada treino, o desvio por horário e por bairro, o ritmo de cada entregador e as últimas entregas. Também tem um botão para treinar o modelo.

Instalação em um banco existente, uma única vez, **depois** da migração anterior:

```sh
mysql -u USUARIO -p guiartcc < database/migrations/2026_11_18_rastreio_rota_inteligencia.sql
```

### Dados de demonstração para a tela Inteligência

Para apresentar a tela antes de haver entregas reais suficientes:

```sh
php scripts/seed_demo_inteligencia.php               # ~4 semanas simuladas em todas as empresas com entregadores
php scripts/seed_demo_inteligencia.php --empresa=21  # só uma empresa (também aceita --dias=N e --por-dia=N)
php scripts/seed_demo_inteligencia.php --remover     # apaga a demonstração e retreina só com dados reais
```

- **Como os dados são gerados:** o seed usa os entregadores e bairros reais de cada empresa, simula picos de almoço e jantar e treina o modelo semana a semana, como aconteceria no uso real.
- **Onde ficam:** as entregas simuladas usam `id_pedido` a partir de 900000000 e não aparecem na tabela `pedido`.
- **Dados reais:** nunca são alterados.
- **Atenção:** o modelo é um só para todas as lojas. Enquanto a demonstração estiver no banco, as previsões reais também usam o modelo treinado com ela. **Rode `--remover` depois de apresentar.**

O **GUIAR** é um sistema web desenvolvido com foco em tecnologia e inovação, integrando **Internet das Coisas (IoT)** para oferecer uma solução moderna, automatizada e eficiente.

O projeto tem como objetivo unir aplicações web com dispositivos inteligentes, proporcionando monitoramento, automação e controle em tempo real.

---
## 🌐 Demonstração
👉 Acesse o site online:
https://guiartccrotas.serv00.net

---

## 🚀 Objetivo do Projeto

Desenvolver uma plataforma capaz de integrar sistemas web com dispositivos IoT, permitindo:

- Monitoramento em tempo real
- Automação de processos
- Comunicação entre dispositivos e sistema
- Interface amigável para o usuário

---

## 🧠 Conceito de IoT no Projeto

A **Internet das Coisas (IoT)** permite que dispositivos físicos se conectem à internet e troquem dados.

No projeto GUIAR, a IoT é utilizada para:

- 📡 Coleta de dados por sensores  
- 🔌 Controle de dispositivos remotamente  
- 📊 Envio de informações para o sistema web  
- ⚙️ Automação de ações baseadas em eventos  

---

## 🛠️ Tecnologias Utilizadas

### 💻 Web
- HTML5  
- CSS3  
- JavaScript  
- PHP  
- MySQL  

### 🔗 IoT
- ESP32  
- Sensores (temperatura, presença, etc.)  
- Comunicação via API / HTTP / JSON  

### ⚙️ Ferramentas
- XAMPP  
- Git e GitHub  

---

## ⚙️ Funcionalidades

- Sistema web completo com interface amigável  
- Integração com dispositivos IoT  
- Monitoramento de dados em tempo real  
- Automação de ações  
- Comunicação entre hardware e software  
- Armazenamento de dados em banco  

---
