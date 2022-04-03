# getEmailFromLetter

Механизм работы:

1. Создается лид.
2. Запускается поиск дел с типом "Входящее письмо". Если дело найдено, то получаем тело письма
3. С помощью регулярных выражений находим почту клиента, которая содержится в теле письма. Исключаем почты сервиса, откуда пришло письмо. К примеру "all-pribors.ru".
4. Результатом отработки является записанный Email клиента из письма в стандартное поле лида Битрикс24. После того, как удалось записать Email клиента в карту лида, запускается скрипт поиска дубликатов: https://github.com/thnik911/duplicate

Как запустить:

1. getEmailFromLetter.php и auth.php необходимо разместить на хостинге с поддержкой SSL.
2. В разделе "Разработчикам" необходимо создать входящий вебхук с правами на CRM (crm). Подробнее как создать входящий / исходящий вебхук: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=99&LESSON_ID=8581&LESSON_PATH=8771.8583.8581
3. Полученный "Вебхук для вызова rest api" прописать в auth.php.
4. В строке 110 скрипта duplicate.php в 'TEMPLATE_ID' необходимо указать ID бизнес-процесса, который необходимо запустить для дальнейшей отработки лида
5. Делаем POST запрос посредством конструкции Webhook* через робот, или бизнес-процессом: https://yourdomain.com/path/getEmailFromLetter.php?lead=123

Переменные передаваемые в POST запросе:

yourdomain.com - адрес сайта, на котором размещены скрипты auth.php и duplicate.php с поддержкой SSL.

path - путь до скрипта.

lead - ID лида.


*Подробнее о действии Webhook: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=57&LESSON_ID=8551
