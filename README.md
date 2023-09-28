1. Cài docker desktop (https://docs.docker.com/desktop/)
2. Clone git demo project: (https://github.com/nkvuphuong/lamp-app)
3. Tại thư mục gốc project chạy lệnh: "docker compose up -d --build"
4. Mở Docker Desktop -> Containers -> php-apache -> Mở tab "Terminal" -> Chạy lệnh "composer install"
5. Source demo nằm trong thư mục "/demo"
    - Thư mục "/demo/rabbit/": Các demo cơ bản của Rabbit
    - Thư mục "/demo/redis_queue/": Các demo cơ bản của Redis Queue
    - Thư mục "/demo/reveiving_orders/": Demo theo kịch bản đơn vào hệ thống từ File import, Web/App, API
6. Cách run scripts: Mở Docker Desktop -> Containers -> php-apache -> Mở tab "Terminal" -> Chạy lệnh "php <<file_path>>"
   Example: php demo/rabbitmq/1_hello_world/send.php
Note: có thể mở nhiều external terminal cùng lúc để xem quá trình publish/consume bằng cách bấm vào link "Open in external terminal" ở gốc trên bên phải của php-apache terminal