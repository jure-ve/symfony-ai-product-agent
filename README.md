# Symfony AI: Agente de Chat de Productos

Este repositorio es una implementación de referencia para Symfony AI (versión 0.6), demostrando cómo construir agentes inteligentes con herramientas (tools) directamente en un entorno PHP.

El proyecto consiste en un asistente de chat para una tienda virtual capaz de razonar sobre las preguntas del usuario y consultar datos técnicos en tiempo real mediante herramientas personalizadas.

## Stack Tecnológico

- Symfony 8.0 / PHP 8.4
- Symfony AI Bundle (experimental v0.6)
- Plataforma Groq AI (compatible con OpenAI mediante Generic Bridge)
- Modelo Llama 3.3 Versatile

## Características Principales

- **Framework de Agentes**: Uso de la interfaz AgentInterface de Symfony para gestionar conversaciones y la ejecución de herramientas.
- **Llamadas a Herramientas (Tool Calling)**: Implementación de ProductPriceTool usando el atributo #[AsTool] para obtener precios de un repositorio.
- **Integración de Plataforma Genérica**: Conexión a la API de alto rendimiento de Groq utilizando el puente de plataforma genérico.
- **Catálogo de Modelos Fallback**: Configuración avanzada de servicios para admitir nombres de modelos no estándar en el ecosistema Symfony AI.

## Configuración e Instalación

1. Clona el repositorio e instala las dependencias:
   ```bash
   composer install
   ```

2. Configura tus variables de entorno en `.env.local`:
   ```dotenv
   GROQ_API_KEY=tu_api_key_aqui
   ```

3. Asegúrate de que el servidor local esté en ejecución:
   ```bash
   symfony server:start -d
   ```

## Arquitectura de Configuración

### Registro de Servicios

Para permitir el uso de modelos de Groq (que no están en el catálogo estándar de OpenAI), registramos un FallbackModelCatalog en `config/services.yaml`:

```yaml
services:
    ai.platform.model_catalog.generic.fallback:
        class: Symfony\AI\Platform\Bridge\Generic\FallbackModelCatalog
```

### Configuración del AI Bundle

La plataforma se configura en `config/packages/ai.yaml` para apuntar al endpoint compatible con OpenAI de Groq:

```yaml
ai:
    platform:
        generic:
            groq:
                base_url: 'https://api.groq.com/openai'
                api_key: '%env(GROQ_API_KEY)%'
                model_catalog: 'ai.platform.model_catalog.generic.fallback'
    agent:
        default:
            platform: 'ai.platform.generic.groq'
            model: 'llama-3.3-70b-versatile'
```

## Uso

Puedes interactuar con el agente a través del siguiente endpoint de API:

**POST /api/chat**

Ejemplo de petición:
```bash
curl -X POST http://127.0.0.1:8000/api/chat \
     -H "Content-Type: application/json" \
     -d '{"question": "¿Cuál es el precio del SKU-100?"}'
```

Ejemplo de respuesta:
```json
{
  "answer": "El Smartphone X (SKU: SKU-100) cuesta 799.99 EUR."
}
```

## Licencia

Este proyecto está bajo la Licencia MIT.
