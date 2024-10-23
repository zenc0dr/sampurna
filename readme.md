# Sampurna

Sampurna in Indian languages like Sanskrit and Hindi, "sampurna" (सम्पूर्ण) translates to "complete" or "perfect". It is a library that provides a project with a simple task broker, queue, background threads, managed caching and other features required in various projects.

##### Создать хранилище (Базу sqlite)
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

##### Обновить хранилище
```php
sampurna()->vault('test_vault')->update(function ($schema) {
    $schema->table('table2', function (Blueprint $table) {
        $table->integer('count')->default(0)->after('name');
    });
});
```