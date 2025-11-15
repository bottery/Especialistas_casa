-- Migración SAFE: Verificar y crear solo lo que no existe

-- Crear tabla notificaciones
CREATE TABLE IF NOT EXISTS `notificaciones` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `from_user_id` INT UNSIGNED NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `action_url` VARCHAR(255) NULL,
    `read` TINYINT(1) DEFAULT 0,
    `read_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_read` (`read`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_user_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla chats
CREATE TABLE IF NOT EXISTS `chats` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT UNSIGNED NOT NULL,
    `user1_id` INT UNSIGNED NOT NULL,
    `user2_id` INT UNSIGNED NOT NULL,
    `last_activity` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_solicitud` (`solicitud_id`),
    INDEX `idx_users` (`user1_id`, `user2_id`),
    INDEX `idx_activity` (`last_activity`),
    FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user1_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user2_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_chat` (`solicitud_id`, `user1_id`, `user2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla chat_messages
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `chat_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `text` TEXT NOT NULL,
    `attachment_url` VARCHAR(255) NULL,
    `read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    INDEX `idx_chat` (`chat_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`chat_id`) REFERENCES `chats`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla chat_typing
CREATE TABLE IF NOT EXISTS `chat_typing` (
    `chat_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `is_typing` TINYINT(1) DEFAULT 0,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`chat_id`, `user_id`),
    INDEX `idx_updated` (`updated_at`),
    FOREIGN KEY (`chat_id`) REFERENCES `chats`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Tablas creadas correctamente' AS status;
