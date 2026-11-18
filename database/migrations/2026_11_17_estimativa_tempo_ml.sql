-- Estimativa de tempo de entrega com machine learning
--
-- Rode uma vez em cada banco existente (local e serv00), por exemplo no phpMyAdmin
-- ou com:  mysql -u USUARIO -p guiartcc < database/migrations/2026_11_17_estimativa_tempo_ml.sql
--
-- `entrega_viagem` é o histórico usado para treinar o modelo. Ela guarda uma cópia dos dados
-- do pedido (bairro, destino) e NÃO tem chave estrangeira para `pedido`, porque
-- "Finalizar turno" apaga os pedidos entregues e o histórico precisa sobreviver.

CREATE TABLE IF NOT EXISTS `entrega_viagem` (
  `id_viagem` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_entregador` int(11) NOT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `origem_lat` double NOT NULL,
  `origem_lng` double NOT NULL,
  `destino_lat` double NOT NULL,
  `destino_lng` double NOT NULL,
  `distancia_osrm_m` double NOT NULL COMMENT 'Distância da rota calculada pelo OSRM no início',
  `duracao_osrm_s` double NOT NULL COMMENT 'Tempo calculado pelo OSRM no início (sem trânsito)',
  `fator_previsto` double NOT NULL DEFAULT 1 COMMENT 'Fator aplicado pelo modelo sobre o OSRM',
  `duracao_prevista_s` double NOT NULL COMMENT 'Tempo mostrado ao entregador no início',
  `id_modelo` int(11) DEFAULT NULL COMMENT 'Versão do modelo usada (NULL = OSRM puro)',
  `iniciado_em` datetime NOT NULL COMMENT 'Horário local (America/Sao_Paulo)',
  `chegou_em` datetime DEFAULT NULL COMMENT 'Quando o GPS detectou o entregador no destino',
  `concluido_em` datetime DEFAULT NULL COMMENT 'Quando tocou em Concluir Entrega',
  `distancia_final_m` double DEFAULT NULL COMMENT 'Distância até o destino no momento da conclusão',
  `duracao_real_s` int(11) DEFAULT NULL COMMENT 'Início → chegada (ou conclusão, se a chegada não foi detectada)',
  `status` enum('em_andamento','concluida','descartada') NOT NULL DEFAULT 'em_andamento',
  `motivo_descarte` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id_viagem`),
  KEY `idx_viagem_entregador_status` (`id_entregador`, `status`),
  KEY `idx_viagem_pedido` (`id_pedido`),
  KEY `idx_viagem_status_inicio` (`status`, `iniciado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `eta_modelo` (
  `id_modelo` int(11) NOT NULL AUTO_INCREMENT,
  `treinado_em` datetime NOT NULL,
  `amostras` int(11) NOT NULL,
  `parametros` longtext NOT NULL COMMENT 'JSON com colunas, pesos, lambda e smearing',
  `amostras_teste` int(11) DEFAULT NULL,
  `mae_osrm_s` double DEFAULT NULL COMMENT 'Erro médio absoluto do OSRM puro no conjunto de teste',
  `mae_modelo_s` double DEFAULT NULL COMMENT 'Erro médio absoluto do modelo no conjunto de teste',
  `mape_osrm` double DEFAULT NULL,
  `mape_modelo` double DEFAULT NULL,
  PRIMARY KEY (`id_modelo`),
  KEY `idx_modelo_treinado` (`treinado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
