import os
import urllib.request
import ctypes
import sys

# URL de la lista de nodos de salida de Tor
TOR_EXIT_NODES_URL = "https://www.dan.me.uk/torlist/?full"

def is_admin():
    """Verifica si el script se está ejecutando con privilegios de administrador."""
    try:
        return ctypes.windll.shell32.IsUserAnAdmin()
    except:
        return False

def block_tor_exit_nodes_windows():
    if not is_admin():
        print("ERROR: Se requieren privilegios de administrador para modificar el firewall.")
        print("Por favor, ejecuta este script desde una terminal (PowerShell/CMD) abierta como 'Administrador'.")
        sys.exit(1)

    print("Descargando la lista de nodos de salida de Tor...")
    try:
        response = urllib.request.urlopen(TOR_EXIT_NODES_URL)
        ip_list = response.read().decode('utf-8').splitlines()

        print(f"Bloqueando {len(ip_list)} direcciones IP de Tor en Windows...")
        for ip in ip_list:
            if ip.strip():
                # Comando de PowerShell para bloquear la IP
                powershell_command = f"New-NetFirewallRule -DisplayName 'Bloquear Tor {ip}' -Direction Inbound -RemoteAddress '{ip}' -Action Block -Protocol Any -Profile Any"
                os.system(f"powershell.exe -Command \"{powershell_command}\"")

        print("Bloqueo de Tor completado en Windows.")
    except Exception as e:
        print(f"Ocurrió un error: {e}")

if __name__ == "__main__":
    block_tor_exit_nodes_windows()