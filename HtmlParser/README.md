Т.З. PHP парсер html страницы (на входе url), который на выходе будет отображать количество и название всех 
используемых html тегов.

На вход контроллер принимает request в котором должен быть параметр url.
В конфиг необходимо записать тип получения контента Curl или FileGetContents
(не обязательный параметр, по усолчанию будет curl), кастомизировать его в енам 
(он тут нафиг не нужен, сделал ~~просто потому что могу~~ тупо показать, 
что я в курсе про них =) ).

Делал на php8.1, laravel 9.0

Абстракции, енамы, фабричный метод притянуты за уши, но я так понимаю, что
в задании именно это и просили сделать.

Пример вывода заскриншотил и добавил в репу.
Урл на котором тестировал https://huntflow.ru/blog/test-task/ (просто какой-то рандомный)