-- Migration: Tabela para tokens de recuperação de senha dos entregadores
-- Data: 2026-11-24
-- Descrição: Cria tabela para armazenar tokens de recuperação de senha dos entregadores

CREATE TABLE IF NOT EXISTS `password_resets_entregador` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expiry` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_token_expiry` (`token`, `expiry`),
  FOREIGN KEY (`user_id`) REFERENCES `entregador`(`id_entregador`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice para limpeza automática de tokens expirados
CREATE EVENT IF NOT EXISTS `cleanup_expired_entregador_tokens`
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM `password_resets_entregador` WHERE `expiry` < NOW();
