git init
git add .
git config --global user.name "Luisaldamaeng"
git config --global user.email "luisaldama41@gmail.com"
git commit -m "Versión inicial del proyecto"
git push -u "https://github.com/Luisaldamaeng/pv" main
Imagina que has hecho algunos cambios en tus archivos (por ejemplo, modificaste index.php).

  <#Paso 1: Revisa tus cambios


  Para ver qué archivos has modificado, usa:
  `
  git status
  `
  Git te mostrará una lista de los archivos modificados en rojo.

  Paso 2: Prepara los archivos para guardarlos

  Este comando agrega tus cambios a un área de "preparación". Puedes agregar archivos uno por uno o todos juntos.


   * Para agregar todos los archivos modificados (lo más común):
  Esto crea una "instantánea" o versión de tus archivos preparados. Es crucial escribir un mensaje claro que describa qué cambiaste.
  `
  git commit -m "Agregué un nuevo título a la página principal"
  `

  Paso 4: Sube tus cambios a GitHub

  Finalmente, este comando envía tus "commits" (las versiones que guardaste) a la nube de GitHub.

  `
  git push
  `
  (Como usaste -u la primera vez, ahora solo necesitas este comando simple).#>