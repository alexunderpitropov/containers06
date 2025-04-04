# Лабораторная работа №6: Работа с Docker-контейнерами и сетями

## Студент

**Питропов Александр, группа I2302**  
**Дата выполнения: _04.04.2025_**


## Цель работы
Выполнив данную работу, студент сможет управлять взаимодействием нескольких контейнеров.

---

## Задание
Создать PHP-приложение на базе двух контейнеров: `nginx` и `php-fpm`.

---

## Подготовка
- Установлен Docker на компьютере.
- Выполнена лабораторная работа №3.

---

## Выполнение

- Создан репозиторий `containers06` и склонирован на ПК:

```bash
git clone git@github.com:alexunderpitropov/containers06.git
```

### .gitignore
Создан файл `.gitignore` в корне проекта со следующим содержимым:

```gitignore
# Ignore files and directories
mounts/site/*
```

---

### Конфигурация nginx (mounts/nginx/default.conf)

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    location ~ \.php$ {
        fastcgi_pass backend:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

### Запуск и тестирование

#### 1. Создание сети:
```bash
docker network create internal
```

![image](https://i.imgur.com/bFd6WtY.jpeg)


#### 2. Запуск контейнера backend:
```bash
docker run -d `
  --name backend `
  --network internal `
  -v ${PWD}\mounts\site:/var/www/html `
  php:7.4-fpm
```

![image](https://i.imgur.com/qzzpzBq.jpeg)

#### 3. Запуск контейнера frontend:
```bash
docker run -d `
  --name frontend `
  --network internal `
  -v ${PWD}\mounts\site:/var/www/html `
  -v ${PWD}\nginx\default.conf:/etc/nginx/conf.d/default.conf `
  -p 80:80 `
  nginx:1.23-alpine
```

![image](https://i.imgur.com/mq1GpdN.jpeg)


---

### Проверка в браузере

Открой в браузере адрес:

```
http://localhost
```

![image](https://i.imgur.com/0LxekER.jpeg)

---

## Ответы на вопросы

**1. Каким образом в данном примере контейнеры могут взаимодействовать друг с другом?**  
Контейнеры взаимодействуют друг с другом благодаря созданной пользователем сети Docker (`internal`). Когда контейнеры подключаются к одной и той же сети, они могут обмениваться данными, используя внутренние DNS-имена (имена контейнеров). Это позволяет одному контейнеру обращаться к другому по имени, без необходимости знать его IP-адрес. В нашем случае `nginx` знает, что для обработки PHP-запросов нужно обратиться к контейнеру `backend`, где работает `php-fpm`.

**2. Как видят контейнеры друг друга в рамках сети internal?**  
В Docker-сети типа `bridge`, которую мы создали с именем `internal`, каждый контейнер получает собственное DNS-имя, совпадающее с его именем. Это означает, что контейнер `frontend` может обращаться к контейнеру `backend` по имени `backend`, как если бы это был адрес сервера. Docker сам решает проблему маршрутизации и сетевого разрешения. Это удобно и безопасно, так как контейнеры из других сетей не смогут получить доступ к сервисам внутри `internal`.

**3. Почему необходимо было переопределять конфигурацию nginx?**  
По умолчанию стандартный образ `nginx` предназначен только для обслуживания статического контента и не содержит настроек для обработки PHP-файлов. Чтобы `nginx` умел перенаправлять PHP-запросы на `php-fpm`, нужно явно указать это в конфигурации. В частности, блок `location ~ \.php$` указывает, что при запросе PHP-файла нужно перенаправить его на контейнер `backend`, использующий порт 9000 — стандартный порт `php-fpm`. Без этого `nginx` просто предложит скачать `.php` файл или вернет ошибку.

---

## Выводы

В ходе выполнения лабораторной работы была реализована связка из двух Docker-контейнеров, обеспечивающих полноценную работу PHP-приложения. Были успешно созданы и запущены контейнеры `php-fpm` (backend) и `nginx` (frontend), взаимодействующие между собой через специально созданную изолированную сеть `internal`.

Работа позволила закрепить практические навыки по:
- созданию и использованию пользовательских Docker-сетей;
- взаимодействию контейнеров через внутренние DNS-имена;
- подключению томов и пробросу конфигурационных файлов;
- ручному запуску контейнеров с нужными параметрами без использования docker-compose.

Также была изучена структура конфигурационного файла `nginx`, необходимая для корректной обработки PHP-запросов и передачи их через FastCGI на `php-fpm`.

В результате был развернут простой, но рабочий стек веб-сервера с возможностью последующего масштабирования или интеграции в более сложные системы. Работа позволяет лучше понять принципы микросервисной архитектуры и контейнеризации веб-приложений.

## Библиография

- [Docker Docs – Networking Overview](https://docs.docker.com/network/) - В официальной документации Docker подробно описаны типы сетей (bridge, host, overlay и т.д.), способы создания пользовательских сетей, взаимодействие контейнеров по имени, настройка DNS внутри Docker-сетей. Это основной источник по теме сетевого взаимодействия между контейнерами.
- [Docker: Создание сети с флагом --internal](https://docs.docker.com/reference/cli/docker/network/create/) - Официальная документация Docker, объясняющая использование команды docker network create с различными опциями, включая флаг --internal.
- [Обзор сетей в Docker](https://docs.docker.com/engine/network/) - Раздел официальной документации Docker, посвященный различным типам сетей, их созданию и использованию.
- [Docker Docs – Use volumes](https://docs.docker.com/storage/volumes/) - Официальное руководство по работе с томами (volumes) и монтированием локальных директорий в контейнеры. Рассматриваются типы томов, команды для их подключения, а также примеры использования.
- [Docker Hub – php:7.4-fpm](https://hub.docker.com/_/php) - Страница официального образа PHP. Здесь указаны теги (версии), переменные среды, способы подключения и примеры использования PHP-FPM. Полезно для понимания, как использовать этот образ для обработки PHP-кода в контейнерах.
- [Docker Hub – nginx](https://hub.docker.com/_/nginx) - Описание официального образа Nginx. Объясняется, как настраивать конфигурационные файлы, подключать тома, работать с пользовательскими конфигами и пробрасывать порты. Также указаны типичные сценарии использования.
- [Nginx Docs – Beginner’s Guide](https://nginx.org/en/docs/beginners_guide.html) - Руководство для начинающих по работе с Nginx. Объясняет основы конфигурации, настройку виртуальных хостов, маршрутизацию запросов, а также взаимодействие с FastCGI (например, PHP-FPM).
- [PHP Manual – PHP-FPM (FastCGI Process Manager)](https://www.php.net/manual/en/install.fpm.php) - Официальная документация PHP по установке и работе с PHP-FPM. Описывает его архитектуру, настройку пула процессов, взаимодействие с веб-серверами через FastCGI.
