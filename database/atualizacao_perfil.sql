-- Execute em bancos existentes para habilitar a foto da área "Minha conta".
-- O histórico de pedidos usa as tabelas atuais e não precisa duplicar dados.

USE droz_robotica;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @coluna_foto_existe = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'foto_perfil'
);

SET @sql_foto = IF(
    @coluna_foto_existe = 0,
    'ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL AFTER ativo',
    'SELECT ''Coluna foto_perfil já existe'' AS aviso'
);

PREPARE stmt_foto FROM @sql_foto;
EXECUTE stmt_foto;
DEALLOCATE PREPARE stmt_foto;

SELECT 'Atualização de perfil aplicada com sucesso.' AS resultado;
