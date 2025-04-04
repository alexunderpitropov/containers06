<!DOCTYPE html>
<html lang="ru">
<head>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Галерея персонажей игр</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="#">О персонажах</a></li>
                <li><a href="#">Новости</a></li>
                <li><a href="#">Контакты</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>#Персонажи игр</h1>
        <p>Откройте для себя любимых персонажей из популярных игр</p>
        <div class="gallery">
            <?php
            $dir = 'images/';  // Папка с изображениями персонажей
            $files = scandir($dir);
            if ($files !== false) {
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'jpg') {
                        echo "<div class='gallery-item'><img src='$dir$file' alt='Game Character'></div>";
                    }
                }
            }
            ?>
        </div>
    </main>

    <footer>
        <p>USM © 2024</p>
    </footer>
</body>
</html>
