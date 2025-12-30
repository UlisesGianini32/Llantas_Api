<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Import</title>
</head>
<body>

<h1>Prueba de subida</h1>

<form action="{{ route('llantas.importar') }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    <input type="file" name="archivo" required>

    <button type="submit">
        Subir
    </button>

</form>

</body>
</html>
