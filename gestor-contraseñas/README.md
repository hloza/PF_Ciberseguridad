# Gestor de Contraseñas - Proyecto Final Ciberseguridad

## Descripcion
gestor de contraseñas hecho en PHP que usa encriptacion para guardar las passwords de forma segura.

## Caracteristicas
- Contraseña maestra para acceder
- Encriptacion AES-256 para las passwords
- Base de datos SQLite (facil de usar)
- Interfaz web sencilla 
- Funciones basicas: agregar, listar, editar y eliminar contraseñas

## Como usar
1. Copiar archivos a servidor local (XAMPP, WAMP, etc)
2. Abrir en navegador
3. Crear contraseña maestra al primer acceso
4. Ya se puede usar!

## Estructura del proyecto
```
gestor-contraseñas/
├── src/
│   ├── config/          # configuracion bd
│   ├── modelos/         # modelo de datos
│   ├── controladores/   # logica de negocio  
│   ├── vistas/          # interfaces de usuario
│   ├── includes/        # archivos comunes
│   └── utilidades/      # funciones auxiliares
├── assets/              # css y js
└── data/               # base de datos
```

## Tecnologias usadas
- PHP 7.4+
- SQLite
- HTML5/CSS3
- JavaScript vanilla

## Notas
- Se implementaron conceptos de criptografia simetrica y hash
- La seguridad fue la prioridad principal
