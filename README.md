Demostración básica de como usar el API de Iris.

![image](https://github.com/FrunoDesarrollo/consulta_api_iris/assets/112980298/3c193047-df74-4115-8777-0b1911e17c7b)


Podman necesita un proveedor de compose. Instalar podman-compose (si no lo tiene instalado)
```powershell
pip install podman-compose
```

Asegúrate de que la máquina Podman esté iniciada
```powershell
podman machine list
podman machine start
```


```powershell
# Navega a la carpeta con tu docker-compose.yaml
cd C:\ruta\a\tu\proyecto

# Ejecutar
podman-compose up -d

# Ver estado
podman-compose ps

# Ver logs
podman-compose logs -f

# Detener
podman-compose down
```

## Probar la aplicación

Una vez levantado, abre en el navegador:
```
http://localhost:8080
```


