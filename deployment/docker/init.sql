# Script de inicialización de la base de datos
# Este archivo se ejecuta automáticamente cuando se crea el contenedor de PostgreSQL

\echo 'Inicializando base de datos para POS Cooperadora...'

-- Crear extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- Configurar timezone
SET timezone = 'America/Argentina/Buenos_Aires';

\echo 'Base de datos inicializada correctamente.'
