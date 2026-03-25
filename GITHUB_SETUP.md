# 🚀 Instrucciones para GitHub

## Credenciales del Sistema
- **Usuario**: sistemas@odontologia.unt.edu.ar
- **Contraseña**: Github@ferrero.net

## Pasos para conectar con GitHub

### 1. Crear repositorio en GitHub
1. Ir a https://github.com
2. Iniciar sesión con las credenciales proporcionadas
3. Hacer clic en "New Repository"
4. Nombre: `sistema-pos-cooperadora`
5. Descripción: `Sistema de Punto de Venta para la Cooperadora de la Facultad de Odontología`
6. Seleccionar "Private Repository"
7. NO inicializar con README (ya lo tenemos)
8. Hacer clic en "Create Repository"

### 2. Conectar repositorio local con GitHub

```bash
# Agregar remote origin
git remote add origin https://github.com/sistemas@odontologia.unt.edu.ar/sistema-pos-cooperadora.git

# Subir al repositorio remoto
git branch -M main
git push -u origin main
```

### 3. Configurar para desarrollo colaborativo

```bash
# Crear rama de desarrollo
git checkout -b develop
git push -u origin develop

# Crear rama para nuevas funcionalidades
git checkout -b feature/integracion-afip
```

## Estructura de Ramas Sugerida

- **main**: Código de producción estable
- **develop**: Rama de desarrollo principal
- **feature/**: Nuevas funcionalidades
- **hotfix/**: Correcciones urgentes en producción
- **release/**: Preparación de releases

## Comandos Git Útiles

```bash
# Ver estado
git status

# Ver historial
git log --oneline

# Crear nueva rama
git checkout -b feature/nueva-funcionalidad

# Cambiar de rama
git checkout main

# Merge de rama
git merge feature/nueva-funcionalidad

# Actualizar desde remoto
git pull origin main

# Subir cambios
git add .
git commit -m "mensaje descriptivo"
git push origin nombre-rama
```

## Configuración de Producción

### Variables de entorno para GitHub Actions (opcional)

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [main]

env:
  DB_HOST: ${{ secrets.DB_HOST }}
  DB_DATABASE: ${{ secrets.DB_DATABASE }}
  DB_USERNAME: ${{ secrets.DB_USERNAME }}
  DB_PASSWORD: ${{ secrets.DB_PASSWORD }}
```

### Secrets a configurar en GitHub:
- `DB_HOST`
- `DB_DATABASE` 
- `DB_USERNAME`
- `DB_PASSWORD`
- `SSH_PRIVATE_KEY` (para deploy automático)

---

¡El proyecto está listo para GitHub! 🎉
