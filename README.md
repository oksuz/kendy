# Kendy Framework

Kendy bir `benim kendi framewörküm var` projesidir. 

## Kurulum

Paket yönetimi ve autoloading için `composer` kullanmaktayız. `Composer` kurulumu için [bu](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) adresi ziyaret edebilirsiniz.

Kurulumu yaptıktan sonra aşağıdaki komutu verin:

```bash
composer install
```

kurulum esnasında sistem sizden [Configuration.php.dist](./app/Config/Configuration.php.dist) 'de tanımlı olan değerleri isteyecek ve uygulama için 
gerekli olan konfigurasyonu oluşturacaktır.

## Kullanılan 3. Parti Komponentler
*   [Monolog](https://github.com/Seldaek/monolog)
*   [Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html)


# Bağımlılık Yönetimi (Dependency Injection)

Proje içerisinde [container builder](./app/Builder/ContainerBuilder.php) barındırmaktadır.
Bu containerBuilder [services.json'u](./app/Config/services.json) okuyarak tanımlı servisleri inşa eder ve [Container](./app/Container.php) 
içine doldurur. Buda bize container olan scope'lar içinde bu servisleri kullanabilmemizi sağlar.

## services.json
İki ana düğümden oluşan bir yapıya sahiptir. Bu düğümler `parameters` ve `services`'dir. `parameters` bir objedir key-value tutar, `services` ise servis objeleri barındıran bir dizidir.

## Parametre tanımı:

Aşağıda örnek iki parametre tanımı verilmiştir;

```json
{
  "parameters": {
    "application.environment": "$APP_ENV",
    "log.file": "/var/log/app.log"
  },
}
```

`log.file` parametresi'nin karşılığı `/var/log/app.log` olarak verilmiştir. 

Dikkat çekmek istediğimiz bir diğer nokta ise `application.environment` parametresidir.
Burada görmüş olduğunuz gibi `$APP_ENV` adında bir değişken, değer olarak verilmiştir. Container'in compile zamanında bu değer `Application\\Config\\Configuration::APP_ENV` sabitinin değerini alacaktır.
Dolayısı ile `Application\\Config\\Configuration` class'i içinde __tanımlı constant değerleri__, parameters içinde `$DEGER` olarak kullanabiliriz.
Burada dikkat edilmesei gereken iki nokta var

*   Kullanılacak degisken isminin tamami büyük harf olmalı
*   Özel karakter olarak sadece `_` kullanılmalı

### Kod İçerisinden Parametreye Erişmek:

Mevcut yapıda [AbstractController](./app/lib/AbstractController.php?at=master&fileviewer=file-view-default) container'a erişim için `getContainer()` metodunu sunmaktadır. 
Bu demek oluyor ki, controller içinde aşağıdaki gibi parametrelere erişebiliriz,

```php
<?php
// class declaration

public function testAction()
{
    $env = null;
    if ($this->getContainer()->hasParameter("application.environment")) { // eğer var mi kontrolu gerekli ise bu şekilde yapılabilir
        $env = $this->getContainer()->getParameter("application.environment"); // varsa değeri yok ise null döner
    }
}

// ...
```


## Servis Tanımı:

Bir servisin basit olarak tanımı aşağıdaki gibidir;

```json
  "services": [
    {
      "id": "request",
      "class": "\\Library\\Http\\Request"
    }
  ]
```

Görüldüğü gibi bir servis nesnesinin olmazsa olmaz iki parametresi var bunlar, `id` ve `class` 'dir. `id`, container üzerinden erişebileceğimiz alias `class` ise bu alias'a ait class'i temsil etmektedir.

### Başka bir servise bağımlı servisler ve constructor injection

Aşağıdaki gibi bir seneryoyu göz önüne alalım;

```php
<?php
class ConsoleLogger implements Logger
{

    public funtion __construct($name = "logger")
    {
        // ...
    }
    
    public function write($message) 
    {
        echo $message;
    }
}


class Application
{
    public function __construct(Logger $logger)
    {
        // ...
    }
}

// init...
$logger = new ConsoleLogger();
$application = new Application($logger);

```

Yukarıdaki `Application` sınıfı `Logger` interface'inden bir nesneye ihtiyac duyuyor. 

*   bir logger instance'i oluşturuyoruz
*   bir application instance'i oluşturuyoruz
*   application'a logger'i veriyoruz

Peki `ConsoleLogger` yerine `FileLogger` kullanmak istersek ? Kodu modifiye etmek durumunda kalacağız. İşte bu duruma düşmemek ve manuel sınıf çağrıları yapmamak için yapıcı metod üzerinden (constructor) bağımlılığı enjekte edebiliriz. (dependency injection)
`services.json` üzerinde constructor injection aşağıdaki gibi yapılabilmektedir.

```json
"services": [
    {
        "id": "application",
        "class": "\\App\\Application",
        "args": ["@console.logger"]
    },
    
    {
        "id": "console.logger",
        "class": "\\Logger\\ConsoleLogger"
    },
    
    {
        "id": "file.logger",
        "class": "\\Logger\\FileLogger"
    }
]
```

ContainerBuilder Application sınıfını oluştururken, `args` array'ini kontrol edecek ve `@` ile başlayan `@console.logger` id'li bir diğer servisi arayacak, yok ise oluşturmaya çalışacaktır, oluşturduğu taktirde. `application`'a teslim edecektir.
Eğer `file.logger`'a geçmek istersek, tek yapacağımız `@console.logger` yerine `@file.logger` yazmak olacaktır. 

__ÖNEMLİ NOT:__ Örneklerde de göreceğiniz üzere `args`'a verilen servis id'leri `@` ile başlamaktadır.


### Parametreye Bağımlı Servisler ve Parameter Injection

Yukarıdaki örnekte dikkatinizi bir noktaya çekmek istiyorum, `ConsoleLogger`'in constructor metodunda `$name` adında bir parametre var. Bu parametreyi `services.json` üzerinden vermemiz gayet tabi mümkündür;

```json
{
    "parameters": {
        "logger.name": "logger:$APP_ENV"
    },
    "services": [
        {
            "id": "application",
            "class": "\\App\\Application",
            "args": ["@console.logger"]
        },
        
        {
            "id": "console.logger",
            "class": "\\Logger\\ConsoleLogger",
            "args": ["%logger.name%"]
        },
        
        {
            "id": "file.logger",
            "class": "\\Logger\\FileLogger"
        }
    ]
}
```

`"args": ["%logger.name%"]` satırı ile `parameters`'da tanımlı olan değeri servisimize aktaramamız mümkündür. `$APP_ENV` ile ilgili duruma `Parametre Tanımı` bölümünde değinmiştik.

### Değerlerini Metodlar Aracılığı ile Alan Servisler ve Setter Injection

Her servis bağlı olduğu değerleri constructor üzerinden almalıdır diye bir durum sizinde tahmin ettiğiniz gibi olamaz. Servis instance'i oluşturulduktan sonra bir metod aracılığı ile injection yapabiliriz. `Monolog` buna en güzel örneği sunuyor,
`Monolog` instance'i log writer'larini `pushHandler` metodu ile almakta. Bu durumu `services.json` üzerinden aşağıdaki gibi çözüyoruz.

```json
{
  "parameters": {
    "logger.name": "app:$APP_ENV:logger",
    "log.file": "$APP_PATH/app/var/logs/$APP_ENV.log"
  },

  "services": [
    {
      "id": "logger",
      "class": "\\Monolog\\Logger",
      "args": ["%logger.name%"],
      "calls": [
        {"method": "pushHandler", "args": ["@logger.stream.handler"]}
      ]
    },
    {
      "id": "logger.stream.handler",
      "class": "\\Monolog\\Handler\\StreamHandler",
      "args": ["%log.file%", 200]
    }
  ]
}
```

Yukarıda görüldüğü gibi `logger` servisi `constructor`'da isime gerek duymakta bunu `Parameter Injection` ile sağlamaktayız. Ayrıca log yazmak için bir `Handler`'a ihtiyaç duymakta ve bu handler'lari
`pushHandler` metodu ile alıyor. 

İşte bu noktada bizde yukarıdaki gibi `calls` array'ini servis nesnemize ekliyoruz ve içine obje olarak `method` (zorunlu) ve `args` (zorunlu değil) keylerinden oluşan bir obje ekliyoruz.
ContainerBuilder bu kısmı çözerek gerekli cağrıyı argümanlarla birlikte bizim için yapıyor. Bizim logger'a erişmek için yapmamız gereken tek şey tabiki;

```php
<?php
// ContainerAware scope

getContainer()->get("logger"); // olacaktır :)

```

### Factory Metodu Olan Servisler

Bazı servisler bir static metod üzerinden instance veriyor olabilir. Bunun için `factory` düğümünü kullanabiliriz. Buna en güzel örnek ise `Doctrine DBAL` servisidir.

```json
{
  "parameters": {
    "dbal.dsn": "mysql://$DB_USER:$DB_PASS@$DB_HOST/$DB_NAME?charset=utf8",
  },
  "services": [
    {
      "id": "default.database.connection",
      "class": "\\Doctrine\\DBAL\\DriverManager",
      "factory": {
        "method": "getConnection",
        "args": [{"url": "%dbal.dsn%"}]
      }
    },
  ]
}
```

Yukarıdaki örnekte `\\Doctrine\\DBAL\\DriverManager::getConnection` metodu ContainerBuilder tarafından çağırılacak ve 1. argüman olarak bir array("url" => "dsn") gönderilecek.


### Yeni Bir Action Oluşturmak

+   Öncelikle [routes.php](./app/routes.php?at=master) dosyasına url pattern'i gidecegi action ve metodu tanımlıyoruz. Örnek:

```php
<?php
Routes::get("/", "Index@index"); // get ile / dizinine gelindiğinde Controller/IndexController::indexAction 'a gidecek
Routes::post("/save", "Posts@kaydet"); // post ile /save dizinine gelindiğinde Controller/PostsController::kaydetAction 'a gidecek
```

+   Controller class isimlerinde __Controller__ suffix'ini metod isimlerinde ise __Action__ suffixini kullanıyoruz. Aşağıda örnek bir controller tanım verilmiştir:
```php
<?php
// file: app/Controller/IndexController.php
namespace Application\Controller;

use Library\AbstractController;
use Library\Http\JsonResponse;

class IndexController extends AbstractController 
{
    public function indexAction()
    {
        return new JsonResponse();
    }
}
```

+   Artık controller'a tanımladığımız url üzerinden erişebiliriz. 

__ÖNEMLİ:__ Action'lar [AbstractResponse](./app/lib/Http/AbstractResponse.php?at=master&fileviewer=file-view-default) tipinde değerler dönmelidir.


### Genel Bilgiler

+   Controller'lar üzerinden Repository'lere `$this->getRepository("repoName")` ile erişebilirsiz. 
+   Controller üzerinden global $_GET, $_POST gibi değerlere `$this->getRequest()` ile erişebilirsiniz. 
+   Controller scope'unda `$this->getRequest()->query()` `$_GET`'e erişebileceğiniz bir sınıf verir
+   Controller scope'unda `$this->getRequest()->params()` `$_POST`'e erişebileceğiniz bir sınıf verir
+   Controller scope'unda `$this->getRequest()->server()` `$_SERVER`'e erişebileceğiniz bir sınıf verir
+   Controller scope'unda `$this->getRequest()->headers()` header'lara erişebileceğiniz bir sınıf verir.
+   Tüm bu sınıflarin get, set ve all metodlari mevcuttur. Örneğin:

```php
<?php
// Controller scope

$query = $this->getRequest()->query();

$query->get("page"); // url/?page=; eğer tanımlı ise değerini değil ise null dönecektir.

// ya da 

$query->get("page", 5); // page degeri tanımlı ise degerini değilse 5 degerini donecektir.

// ya da 

$queryArray = $query->all(); 
$queryArray["page"]; // bu şekilde kullanım undefined index hatasına neden olabilir. bunun önüne geçmek için empty/isset kontrolu yapılmalı ya da get metodu kullanılmalı.


// ayni kosullar params server ve headers içinde geçerlidir.

```

+   Veritabanı işlemlerini Repositoryler üzerinde yapmaya özen gösteriniz.
+   Birden fazla yerde kullanacağınız işlemler için Helper'ları kullanmaya özen gösteriniz.
+   Text işlemleri Http işlemleri yahut I/O işlemleri gibi ortak kullanılacak işlemler için Util kullanmaya özen gösteriniz. (TextUtil, HttpUtil gibi.)
+   __app/lib__ altında ya da __app/App.php__ üzerinde geliştirme yapmanız durumunda __(önerilmez)__ bu kütüphaneleri kullanan bir çok yer etkilenecektir. Lütfen test yapmayı ihmal etmeyiniz.


### Yapılacaklar
+   Şu anda sistem After/Before callback destekliyor. Yani bir route'a girmeden önce dilersek bir fonksiyon çıkarkende bir fonksiyon çalıştırabiliyoruz. Bunun multi olabilmesi lazım.
+   Error yapısı daha da geliştirilebilir.
+   Gelen datanın validasyonu zor oluyor. belki bir form validation sınıfı yazilabilir. 
+   Şu an için cache provider yok, cache implementasyonu yapılmalı.
+   Parametrik route'ler şu an regex ile çalışıyor bunu etiket şeklinde yapabiliriz. ( şu an : user/(\w+) -> user/yunus , ileride user/:name -> assert(":name" => "\w+") olabilir )