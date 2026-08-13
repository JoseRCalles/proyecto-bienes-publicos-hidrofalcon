<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convertir Excel a CSV</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        #submitBtn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #submitBtn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        #message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        #message.success {
            background-color: #d4edda;
            color: #155724;
        }
        #message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Convertir Excel a CSV para Descargar</h2>
    <form id="uploadForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="excelFile">Selecciona un archivo Excel (.xls, .xlsx, .xlsm):</label>
            <input type="file" id="excelFile" name="excelFile" accept=".xls,.xlsx,.xlsm" required>
        </div>
        <button type="submit" id="submitBtn">Convertir y Descargar CSV</button>
    </form>
    
    <div id="message"></div>
</div>

<script src="./spreadsheet.js"></script>

</body>
</html>