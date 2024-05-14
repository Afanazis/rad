<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera Access</title>
</head>

<body>
    <h1>Capture Photo from Front Camera</h1>
    <video id="video" width="320" height="240" autoplay></video>
    <button id="snap">Capture Photo</button>
    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
    <img id="photo" src="" alt="Captured Image">
    <button id="upload" style="display:none;">Upload Photo</button>

    <script>
        // Accessing the back camera
        navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: {
                        exact: 'environment'
                    }
                }
            })
            .then(stream => {
                const video = document.getElementById('video');
                video.srcObject = stream;
                video.play();
            })
            .catch(err => {
                console.error('Error accessing the camera', err);
            });
        // Accessing the front camera
        navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user'
                }
            })
            .then(stream => {
                const video = document.getElementById('video');
                video.srcObject = stream;
                video.play();
            })
            .catch(err => {
                console.error('Error accessing the camera', err);
            });

        // Capturing the photo
        document.getElementById('snap').addEventListener('click', () => {
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');
            const video = document.getElementById('video');
            context.drawImage(video, 0, 0, 320, 240);
            const dataURL = canvas.toDataURL('image/png');
            document.getElementById('photo').src = dataURL;
            document.getElementById('upload').style.display = 'block';
        });

        // Uploading the photo
        document.getElementById('upload').addEventListener('click', () => {
            const canvas = document.getElementById('canvas');
            const dataURL = canvas.toDataURL('image/png');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = () => {
                if (xhr.status === 200) {
                    alert('Photo uploaded successfully');
                } else {
                    alert('Error uploading photo');
                }
            };
            xhr.send('image=' + encodeURIComponent(dataURL));
        });
    </script>
</body>

</html>

<?php
if (isset($_POST['image'])) {
    $data = $_POST['image'];

    // Menghilangkan "data:image/png;base64,"
    $data = str_replace('data:image/png;base64,', '', $data);
    $data = str_replace(' ', '+', $data);

    // Dekode base64
    $data = base64_decode($data);

    // Menyimpan file ke server
    $file = 'uploads/' . uniqid() . '.png';
    file_put_contents($file, $data);

    // Menyimpan file path ke database
    $host = 'localhost'; // Ganti dengan host database Anda
    $db = 'your_database'; // Ganti dengan nama database Anda
    $user = 'your_username'; // Ganti dengan username database Anda
    $pass = 'your_password'; // Ganti dengan password database Anda

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO photos (file_path) VALUES (?)");
    $stmt->bind_param("s", $file);

    if ($stmt->execute()) {
        echo "Foto berhasil diupload dan disimpan ke database.";
    } else {
        echo "Terjadi kesalahan saat menyimpan ke database: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>