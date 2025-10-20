CREATE TABLE `pessoa` (
    `id` VARCHAR(50) NOT NULL PRIMARY KEY,
    `apelido` VARCHAR(32) NOT NULL UNIQUE,
    `nome` VARCHAR(100) NOT NULL,
    `nascimento` VARCHAR(10) NOT NULL,
    `stack` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
