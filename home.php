<?php
session_start();
if (!isset($_SESSION['token'])) {
    echo "<script>alert('You need to login first'); window.location.href='login.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['name']) && isset($_POST['value'])) {
        $name = $_POST['name'];
        $value = $_POST['value'];

        $url = 'https://tabackendbackup-qt7j3qweia-uw.a.run.app/data';
        $data = json_encode(array("name" => $name, "value" => $value));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $_SESSION['token']
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($response, true);
        echo "<script>alert('".$result['message']."'); window.location.href='home.php';</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    if (isset($_PUT['id']) && isset($_PUT['name']) && isset($_PUT['value'])) {
        $id = $_PUT['id'];
        $name = $_PUT['name'];
        $value = $_PUT['value'];

        $url = 'https://tabackendbackup-qt7j3qweia-uw.a.run.app/data/' . $id;
        $data = json_encode(array("name" => $name, "value" => $value));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $_SESSION['token']
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($response, true);
        echo "<script>alert('".$result['message']."'); window.location.href='home.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - CRUD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .actions button {
            background-color: #28a745;
        }
        .actions button.delete {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Token</h2>
        <p><?php echo htmlspecialchars($_SESSION['token']); ?></p>

        <div style="text-align: right;">
            <form method="post" action="logout.php" style="display: inline;">
                <button type="submit">Logout</button>
            </form>
        </div> 

        
        <h2>CRUD Operations</h2>
        <h3>Create Data</h3>
        <form id="createDataForm" method="post" action="home.php">
            <input type="text" name="name" placeholder="Item" required>
            <input type="text" name="value" placeholder="Harga" required>
            <button type="submit">Create</button>
        </form>

        <h3>Data List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Harga</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $url = 'https://tabackendbackup-qt7j3qweia-uw.a.run.app/data';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $_SESSION['token']
                ));

                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo '<tr><td colspan="4">Error: ' . curl_error($ch) . '</td></tr>';
                }
                curl_close($ch);

                $data = json_decode($response, true);
                if (is_array($data)) {
                    foreach ($data as $item) {
                        echo "<tr>";
                        echo "<td>{$item['id']}</td>";
                        echo "<td>{$item['name']}</td>";
                        echo "<td>{$item['value']}</td>";
                        echo "<td class='actions'>
                            <button class='update' onclick=\"updateData({$item['id']}, '{$item['name']}', '{$item['value']}')\">Update</button>
                            <button class='delete' onclick=\"deleteData({$item['id']})\">Delete</button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function updateData(id, name, value) {
            const newName = prompt("Update Item:", name);
            const newValue = prompt("Update Harga:", value);
            if (newName !== null && newValue !== null) {
                fetch('update.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + '<?php echo $_SESSION['token']; ?>'
                    },
                    body: JSON.stringify({id: id, name: newName, value: newValue})
                })
                .then(response => response.json())
                .then(result => {
                    alert(result.message);
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update data');
                });
            }
        }

        function deleteData(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                fetch('delete.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + '<?php echo $_SESSION['token']; ?>'
                    },
                    body: JSON.stringify({id: id})
                })
                .then(response => response.json())
                .then(result => {
                    alert(result.message);
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete data');
                });
            }
        }
    </script>
</body>
</html>
