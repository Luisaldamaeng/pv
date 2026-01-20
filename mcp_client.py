from fastapi import FastAPI, Depends, HTTPException
import uvicorn
import os
from notion_client import Client
from notion_client.helpers import iterate_paginated_api

app = FastAPI(
    title="Notion MCP Client",
    description="Cliente MCP para interactuar con Notion, exponiendo funcionalidades como herramientas para Gemini.",
    version="0.0.1",
)

# --- Configuración de la API de Notion ---
# Se recomienda usar variables de entorno para las credenciales.
# NOTION_TOKEN se obtiene de la integración de Notion.
# NOTION_DATABASE_ID es el ID de la base de datos de Notion que deseas interactuar.
NOTION_TOKEN = os.getenv("NOTION_TOKEN")
NOTION_DATABASE_ID = os.getenv("NOTION_DATABASE_ID") # Opcional, si siempre interactúas con la misma DB

if not NOTION_TOKEN:
    print("ADVERTENCIA: NOTION_TOKEN no está configurado como variable de entorno. Algunas herramientas de Notion no funcionarán.")

# Dependencia para obtener el cliente de Notion
def get_notion_client():
    if not NOTION_TOKEN:
        raise HTTPException(status_code=500, detail="NOTION_TOKEN no configurado en el servidor.")
    return Client(auth=NOTION_TOKEN)

@app.get("/")
async def root():
    return {"message": "Notion MCP Client is running!"}

@app.get("/tools/list_database_pages", summary="Lista todas las páginas de una base de datos de Notion",
         response_description="Lista de propiedades clave de las páginas de la base de datos de Notion.")
async def list_database_pages(
    database_id: str = NOTION_DATABASE_ID,
    notion: Client = Depends(get_notion_client)
):
    """
    Lista las páginas de una base de datos de Notion, incluyendo sus IDs y títulos (o propiedad 'Name').
    Requiere un `database_id`. Si no se provee, intenta usar NOTION_DATABASE_ID de las variables de entorno.
    """
    if not database_id:
        raise HTTPException(status_code=400, detail="database_id es requerido, ya sea como parámetro o en variables de entorno.")

    try:
        pages = []
        # Usa iterate_paginated_api para manejar automáticamente la paginación
        for page in iterate_paginated_api(notion.databases.query, database_id=database_id):
            page_id = page["id"]
            title_property = page.get("properties", {}).get("Name") or page.get("properties", {}).get("title")

            page_title = "Sin título"
            if title_property:
                if title_property.get("type") == "title" and title_property.get("title"):
                    page_title = "".join([t["plain_text"] for t in title_property["title"]])
                elif title_property.get("type") == "rich_text" and title_property.get("rich_text"):
                    page_title = "".join([t["plain_text"] for t in title_property["rich_text"]])

            pages.append({
                "id": page_id,
                "title": page_title
            })
        return {"pages": pages}
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error al listar páginas de Notion: {e}")

# --- Otros endpoints para herramientas de Notion (futuro) ---
# Ejemplo: recuperar contenido de una página, crear página, etc.


if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)
