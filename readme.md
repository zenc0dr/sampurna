# Sampurna

Sampurna in Indian languages like Sanskrit and Hindi, "sampurna" (सम्पूर्ण) translates to "complete" or "perfect". It is a library that provides a project with a simple task broker, queue, background threads, managed caching and other features required in various projects.
###### Переменные окружения
| Ключ                  | Умолчание | Описание                                                                          |
| --------------------- | --------- | --------------------------------------------------------------------------------- |
| SAMPURNA_PHP_PATH     | php       | Указание интепретатора php                                                        |
| SAMPURNA_NOHUP_ENABLE | false     | Если на хосте есть библиотека nohup то выполнение фоновых процессов более надёжно |
###### Переменные сессии
| Ключ              | Умолчание | Описание                                                           |
| ----------------- | --------- | ------------------------------------------------------------------ |
| sampurna.log.echo | null      | Если перевести в true то глобальный лог будет выводиться в консоль |
#### Хранилища
В качестве системы оперативных хранилищ используется база данный sqlite, так как она имеет особенности которые наилучшим образом проявляются именно в роли динамических баз. Будем в последствии называть эти базы хранилища.
###### Создать хранилище (Базу sqlite)
```php
sampurna()->vault('test_vault')->create(function ($schema) {
    $schema->create('table1', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
    });
    $schema->create('table2', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->string('email');
    });
});
```
###### Обновить хранилище (добавить новые таблицы или поля)
```php
sampurna()->vault('test_vault')->update(function ($schema) {
    $schema->table('table2', function (Blueprint $table) {
        $table->integer('count')->default(0)->after('name');
    });
});
```
>Разница между функциями vault()->create и vault()->update только в том что в случае create база данных полностью удаляется и создаётся заново. Больше никаких отличий, далее создание и изменение структуры базы зависит от $schema (Schema::connection)
###### Сделать запрос в хранилище
```php
# Получим экземпляр хранилища
$vault = sampurna()->vault('waterway');

# Получить экземпляр
$vault->query('ships', 123); # Ниже тоде самое
$vault->query('ships')>find(123);
$vault->query('ships', 'g5nhj-fnnd3-ddfnsc', 'uuid'); # Ниже тоде самое
$vault->query('ships')->where('uuid', 'g5nhj-fnnd3-ddfnsc')->first();

# Добавить запись
$vault->query('ships')->insert([
    'id' => $ship_id,
    'name' => $ship_name,
]);
```
>Это обычная обёртка над QueryBuilder и работа происходит с базой SQLite с именем waterway (storage/sampurna_vault/vaults/**waterway**)

#### Юниты
Описания (манифесты) юнитов находятся в папке заданной в `config/sampurna.php` + `/units`
Например манифест `storage/sampurna_vault/units/azimut.waterway.json`
```json
{
    "name": "Любое имя юнита",
    "desc": "Тут можно описать что делает юнит",
    "call": "App.Services.Parsers.Waterway.WaterwayParser.dispatcher",
}
```

###### Таблица свойств манифеста юнита
| Свойство       | Обязательно | описание                         | Пример                                                  |
| -------------- | ----------- | -------------------------------- | ------------------------------------------------------- |
| name           | да          | Имя юнита                        |                                                         |
| stack          | нет         |                                  |                                                         |
| desc           | да          | Описание юнита                   |                                                         |
| call           | да          | Вызов юнита                      | App.Services.Parsers.Waterway.WaterwayParser.dispatcher |
| attempts_max   | нет         | Максимальное количество попыток  |                                                         |
| attempts_pause | нет         | Пауза в секундах между попытками |                                                         |

^unit-manifest-table

#### Artisan команды
###### Создать новый стэк
```bash
php artisan sampurna:stack create --uuid="test.stack" --name="Тестовый стек"
```
###### Вывести список стэков
```bash
php artisan sampurna:stack list
```
###### Запустить юнита из очереди
```bash
php artisan sampurna:unit run --uuid=unit1:0
```

