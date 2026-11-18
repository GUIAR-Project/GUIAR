-- Rastreio do cliente, rota otimizada e previsão ao vivo
--
-- Rode uma vez em cada banco existente, DEPOIS de 2026_11_17_estimativa_tempo_ml.sql:
--   mysql -u USUARIO -p guiartcc < database/migrations/2026_11_18_rastreio_rota_inteligencia.sql
-- Pode ser executada mais de uma vez sem erro (IF NOT EXISTS).

ALTER TABLE `pedido`
  ADD COLUMN IF NOT EXISTS `telefone_cliente` varchar(20) DEFAULT NULL COMMENT 'WhatsApp do cliente (só dígitos), opcional',
  ADD COLUMN IF NOT EXISTS `token_rastreio` char(32) DEFAULT NULL COMMENT 'Identificador do link público de rastreio',
  ADD COLUMN IF NOT EXISTS `entregue_em` datetime DEFAULT NULL COMMENT 'Quando foi marcado como entregue (horário local)',
  ADD COLUMN IF NOT EXISTS `rota_ordem` int(11) DEFAULT NULL COMMENT 'Posição na rota otimizada do entregador (1 = primeira parada)',
  ADD COLUMN IF NOT EXISTS `rota_trecho_s` int(11) DEFAULT NULL COMMENT 'Tempo previsto (ajustado pelo modelo) do trecho que chega a esta parada';

CREATE UNIQUE INDEX IF NOT EXISTS `idx_pedido_token_rastreio` ON `pedido` (`token_rastreio`);

ALTER TABLE `entrega_viagem`
  ADD COLUMN IF NOT EXISTS `eta_restante_s` int(11) DEFAULT NULL COMMENT 'Último tempo restante informado pelo celular do entregador',
  ADD COLUMN IF NOT EXISTS `distancia_restante_m` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `eta_atualizado_em` datetime DEFAULT NULL;
