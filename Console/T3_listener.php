<?php
////======================================================////
////																										  ////
////            Консольная команда M-пакета					      ////
////																											////
////======================================================////
/**
 *  Что делает
 *  ----------
 *    - Listen to log
 *
 *  Аргументы
 *  ---------
 *
 *    sleeptime     // Период обновления данных в секундах
 *
 *  Опции
 *  -----
 *
 *
 *
 *
 */

//-----------------------------------//
// Пространство имён artisan-команды //
//-----------------------------------//
// - Пример:  M1\Console

  namespace M2\Console;

//---------------------------------//
// Подключение необходимых классов //
//---------------------------------//

  // Базовые классы, необходимые для работы команд вообще
  use Illuminate\Console\Command;

  // Классы, поставляемые Laravel
  use Illuminate\Routing\Controller as BaseController,
      Illuminate\Support\Facades\App,
      Illuminate\Support\Facades\Artisan,
      Illuminate\Support\Facades\Auth,
      Illuminate\Support\Facades\Blade,
      Illuminate\Support\Facades\Bus,
      Illuminate\Support\Facades\Cache,
      Illuminate\Support\Facades\Config,
      Illuminate\Support\Facades\Cookie,
      Illuminate\Support\Facades\Crypt,
      Illuminate\Support\Facades\DB,
      Illuminate\Database\Eloquent\Model,
      Illuminate\Support\Facades\Event,
      Illuminate\Support\Facades\File,
      Illuminate\Support\Facades\Hash,
      Illuminate\Support\Facades\Input,
      Illuminate\Foundation\Inspiring,
      Illuminate\Support\Facades\Lang,
      Illuminate\Support\Facades\Log,
      Illuminate\Support\Facades\Mail,
      Illuminate\Support\Facades\Password,
      Illuminate\Support\Facades\Queue,
      Illuminate\Support\Facades\Redirect,
      Illuminate\Support\Facades\Redis,
      Illuminate\Support\Facades\Request,
      Illuminate\Support\Facades\Response,
      Illuminate\Support\Facades\Route,
      Illuminate\Support\Facades\Schema,
      Illuminate\Support\Facades\Session,
      Illuminate\Support\Facades\Storage,
      Illuminate\Support\Facades\URL,
      Illuminate\Support\Facades\Validator,
      Illuminate\Support\Facades\View;

  // Доп.классы, которые использует эта команда


//--------------------//
// Консольная команда //
//--------------------//
class T3_listener extends Command
{

  //---------------------------//
  // 1. Шаблон artisan-команды //
  //---------------------------//
  //  - '[имя] {user}'        | задать аргумент
  //  - '[имя] {user=foo}'    | задать аргумент с значением по умолчанию
  //  - '[имя] {--queue}'     | задать аргумент-опцию
  //  - '[имя] {--queue=}'    | задать аргумент-опцию со значением
  //  - '[имя] {--queue=foo}' | задать аргумент-опцию со значением по умолчанию
  //  - '[имя] {user : desc}' | задать описание аргументу / опции
  // - TODO: настроить шаблон консольной команды

    protected $signature = 'm2:listener {sleeptime=5}';

  //-----------------------------//
  // 2. Описание artisan-команды //
  //-----------------------------//

    protected $description = 'Listen to log';

  //---------------------------------------------------//
  // 3. Свойства для принятия значений из конструктора //
  //---------------------------------------------------//
  // - TODO: подготовить св-ва для принятия значений из конструктора

    //protected $drip;

  //----------------------------------------------------------//
  // 4. DI и другая подготовка объекта команды в конструкторе //
  //----------------------------------------------------------//
  public function __construct()  // здесь можно сделать DI, например: __construct(DripEmailer $drip)
  {

      // Вызвать конструктор класса Command
      parent::__construct();

      // Записать значение аргумента в св-во $drip
      //$this->drip = $drip;

  }

  //------------------------//
  // 5. Код artisan-команды //
  //------------------------//
  //  - Получение значений аргументов artisan-команды в handle():
  //
  //    - $this->argument()    | извлечь значение аргумента по имени, или массив всех аргументов
  //    - $this->option()      | аналог argument, но без аргументов возвращает массив всех опций
  //
  //  - Осуществление запроса информации у пользователя:
  //
  //    - $this->ask()         | запросить ввод пользователем строки
  //    - $this->secret()      | запросить ввод пользователем строки в безопасном невидимом режиме
  //    - $this->confirm()     | спросить, согласен ли (y/n)
  //    - $this->anticipate()  | дать выбрать из нескольких вариантов + свободный ввод
  //    - $this->choice()      | дать выбрать строго из нескольких вариантов (без сводобного ввода)
  //
  //        $x = $this->ask('Введите строку');
  //        $y = $this->choice('Введите строку', ['1'=>'Вариант №1', '2'=>'Вариант 2'], '1');
  //        $z = $this->confirm('Да?', true);
  //
  //  - Вывод информации в окно терминала:
  //
  //    - $this->info()        | вывести в окно терминала сообщение цвета info
  //    - $this->comment()     | вывести в окно терминала сообщение цвета comment
  //    - $this->question()    | вывести в окно терминала сообщение цвета question
  //    - $this->error()       | вывести в окно терминала сообщение цвета error
  //    - $this->table()       | вывести в окно терминала таблицу данных
  public function handle()
  {

    /**
     * Оглавление
     *
     *  1. Выполнить команду
     *  2. В случае неудачи, вывести текст ошибки
     *  3. В случае успеха, вывести соотв.сообщение
     *
     */


    // 1. Принять аргументы

      // 1.1. Принять
      $sleeptime = $this->argument('sleeptime');

      // 1.2. Провести валидацию
      if(!preg_match("/^[0-9]+$/ui", $sleeptime)) {
        $this->error("Sleeptime must be a number.");
        return;
      }

    // 2. Запустить listener
    $timestamp = 0;
    while(1) {

      // 1] Получить все записи из лога, у которых created_at > $timestamp, но не более 100
      $news = \M2\Models\MD1_log::where('created_at','>',$timestamp)->limit(100)->get();

      // 2] Вывести все $new записи в терминал
      foreach($news->toArray() as $new)
        $this->comment('['.$new['created_at'].'] '.$new['message']);

      // 3] Сохранить дату и время последней записи в $news
      if($news->count() != 0)
        $timestamp = $news->last()->created_at;

      // 4] Подождать $sleeptime секунд
      sleep($sleeptime);

    }

  }

}