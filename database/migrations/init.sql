CREATE TABLE `pessoa` (
    `id_pessoa` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `apelido` VARCHAR(32) NOT NULL,
    `nome` VARCHAR(100) NOT NULL,
    `nascimento` DATE NOT NULL,
    `stack` VARCHAR(32) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;