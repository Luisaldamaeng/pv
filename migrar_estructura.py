import dbfread
import mysql.connector
import os

# --- CONFIGURACIÓN ---
# Modifica estas variables con tus datos

DBF_FILE_PATH = r'C:\xampp\htdocs\busqueda\tablas\producto.dbf' # ¡IMPORTANTE! Cambia esto a la ruta de tu archivo DBF
MYSQL_CONFIG = {
    'host': 'localhost',
    'user': 'root',      # Asegúrate de que sea 'root' si es el usuario por defecto de XAMPP
    'password': '',      # Asegúrate de que esté VACÍA si no has puesto contraseña
    'database': 'almacen' # Cambia esto por el nombre de tu base de datos
}
# --- FIN DE LA CONFIGURACIÓN ---

# Mapeo de tipos de datos de DBF a MySQL.
# Puedes ajustar esto según tus necesidades.
DBF_TO_MYSQL_TYPEMAP = {
    'C': 'VARCHAR',   # Character -> VARCHAR(n)
    'N': 'DECIMAL',   # Numeric -> DECIMAL(width, precision)
    'F': 'FLOAT',     # Float -> FLOAT
    'D': 'DATE',      # Date -> DATE
    'T': 'DATETIME',  # DateTime -> DATETIME
    'L': 'BOOLEAN',   # Logical -> BOOLEAN (o TINYINT(1))
    'M': 'TEXT',      # Memo -> TEXT
}

def get_table_name_from_dbf(file_path):
    """Extrae un nombre de tabla válido del nombre del archivo DBF."""
    base_name = os.path.basename(file_path)
    table_name, _ = os.path.splitext(base_name)
    # Reemplaza caracteres no válidos para nombres de tabla MySQL
    return "".join(c if c.isalnum() else '_' for c in table_name)

def create_table_sql_from_dbf(dbf_table, table_name):
    """Genera la sentencia SQL CREATE TABLE a partir de la estructura del DBF."""
    columns_sql = []
    for field in dbf_table.fields:
        field_type = field.type
        # Limpiar nombre de campo para que sea válido en SQL y usar backticks
        field_name_cleaned = "".join(c if c.isalnum() else '_' for c in field.name)

        if field_type not in DBF_TO_MYSQL_TYPEMAP:
            print(f"ADVERTENCIA: Tipo de dato DBF '{field_type}' no reconocido para el campo '{field.name}'. Se usará VARCHAR(255).")
            mysql_type = 'VARCHAR(255)'
        else:
            mysql_type = DBF_TO_MYSQL_TYPEMAP[field_type]

            # Casos especiales que requieren longitud o precisión
            if mysql_type == 'VARCHAR':
                mysql_type = f"VARCHAR({field.length})"
            elif mysql_type == 'DECIMAL':
                mysql_type = f"DECIMAL({field.length}, {field.decimal_count})"

        columns_sql.append(f"`{field_name_cleaned}` {mysql_type}")

    # Une todas las definiciones de columna
    full_columns_sql = ",\n  ".join(columns_sql)

    # Crea la sentencia final
    create_table_query = f"""
CREATE TABLE IF NOT EXISTS `{table_name}` (
  {full_columns_sql}
);
"""

    return create_table_query

def migrate_structure_and_data():
    """Función principal para leer el DBF, crear la tabla y exportar los datos a MySQL."""
    connection = None # Inicializar connection a None
    try:
        # 1. Leer la estructura del archivo DBF
        print(f"Leyendo la estructura del archivo DBF: {DBF_FILE_PATH}")
        # dbfread.DBF() sin load=True permite iterar sobre los registros
        dbf_table_for_structure = dbfread.DBF(DBF_FILE_PATH, load=False)
    except FileNotFoundError:
        print(f"ERROR: No se pudo encontrar el archivo DBF en la ruta: {DBF_FILE_PATH}")
        return
    except Exception as e:
        print(f"ERROR: Ocurrió un error al leer el archivo DBF: {e}")
        return

    # 2. Generar la sentencia SQL CREATE TABLE
    table_name = get_table_name_from_dbf(DBF_FILE_PATH)
    sql_create_table_query = create_table_sql_from_dbf(dbf_table_for_structure, table_name)

    print("\n--- Sentencia SQL CREATE TABLE Generada ---")
    print(sql_create_table_query)
    print("-------------------------------------------\n")

    # 3. Conectar a MySQL y ejecutar la sentencia CREATE TABLE
    try:
        print("Conectando a la base de datos MySQL...")
        connection = mysql.connector.connect(**MYSQL_CONFIG)
        cursor = connection.cursor()

        print(f"Ejecutando la creación de la tabla `{table_name}`...")
        cursor.execute(sql_create_table_query)
        connection.commit()
        print("¡Éxito! La tabla ha sido creada/verificada correctamente en la base de datos.")

        # --- NUEVA SECCIÓN: EXPORTAR DATOS ---
        print(f"\nIniciando la exportación de datos desde {DBF_FILE_PATH} a la tabla `{table_name}`...")

        # Abrir el DBF para iterar sobre los registros
        dbf_data = dbfread.DBF(DBF_FILE_PATH)

        # Obtener los nombres de los campos originales y sus versiones limpias para SQL
        original_field_names = [field.name for field in dbf_data.fields]
        cleaned_sql_field_names = ["`" + "".join(c if c.isalnum() else '_' for c in name) + "`" for name in original_field_names]

        # Construir la sentencia INSERT
        placeholders = ", ".join(["%s"] * len(original_field_names))
        columns_list = ", ".join(cleaned_sql_field_names)
        insert_sql = f"INSERT INTO `{table_name}` ({columns_list}) VALUES ({placeholders})"

        records_to_insert = []
        for record in dbf_data:
            # Extraer los valores en el orden de los nombres de campo originales
            values = [record[name] for name in original_field_names]
            records_to_insert.append(tuple(values)) # mysql.connector.executemany espera una lista de tuplas

        if records_to_insert:
            print(f"Preparando para insertar {len(records_to_insert)} registros...")
            cursor.executemany(insert_sql, records_to_insert)
            connection.commit()
            print(f"¡Éxito! Se han insertado {len(records_to_insert)} registros en la tabla `{table_name}`.")
        else:
            print("No se encontraron registros en el archivo DBF para insertar.")

    except mysql.connector.Error as err:
        print(f"ERROR de MySQL: {err}")
        if connection:
            connection.rollback() # Deshacer cambios si ocurre un error durante la inserción
    except Exception as e:
        print(f"Ocurrió un error inesperado: {e}")
        if connection:
            connection.rollback()
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
            print("Conexión a MySQL cerrada.")

if __name__ == '__main__':
    migrate_structure_and_data()