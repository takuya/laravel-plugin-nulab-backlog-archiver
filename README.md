## Laravel プロジェクト用 Backlog プラグイン

ヌーラボ・バックログのデータをLaravelデータベースへ取り込む。

### 用途

バックアップ・一括更新・プロジェクト間のデータコピー

`Laravel/Lumen`のデータベースに`nulab backlog`のデータを取り込んで、解析・構成変更を行って、スケジュールで同期をかけたり、一括変更・一括削除などを作るための土台をつくる。バックログのデータを俯瞰し自由に触れる環境を作るためにプラグインを用意した。

## 使い方

最初に、デーベースのマイグレーションとORMモデルを用意します。

#### 初期化コマンド
- database/migrations にマイグレーションファイルが一気に作成されます。
- app/models にEloquentモデルが作成されます。
```shell
artisan backlog:make:migration:all
artisan backlog:make:models:all
```

次に、データをBacklogから取り込みます。

```shell
backlog:save:project xxProjectKeyxxx --with-issue
```

データを取り込んだら、あとは閲覧しながらデータを弄くります。

```shell
## 例 やりたいことをかく
$cli = new BacklogAPIClient($space, $key);
$cli->deleteComment(111,[...]);
```

他のアプリケーションにデータを移行するとか、解約後に簡易的にデータ閲覧するなど、データの囲い込みに負けない目的に使えます。

## 設定

`.env` ファイルに BacklogAPIアクセス用の`キー`と`スペースURL`を書いてください。
```shell
BACKLOG_API_KEY=ITZ***********TJ2RpTWkkGT
BACKLOG_SPACE=https://***.backlog.com/
```

## インストール
```shell
composer config repositories.'takuya/laravel-plugin-nulab-backlog-archiver' \
vcs 'https://github.com/takuya/laravel-plugin-nulab-backlog-archiver'
composer require 'takuya/laravel-plugin-nulab-backlog-archiver'
```

## developing

さくっと試したいとき、開発環境を作りたいときは次のコマンドをコピペしてください。

```shell
## laravel/lumenプロジェクトの作成
composer create-project --prefer-dist laravel/lumen my-dev
cd my-dev 
## master バージョン指定 
composer config minimum-stability dev
composer config prefer-stable true
# インストール
composer config repositories.'takuya/laravel-plugin-nulab-backlog-archiver' \
vcs 'https://github.com/takuya/laravel-plugin-nulab-backlog-archiver'
composer require 'takuya/laravel-plugin-nulab-backlog-archiver':master
composer install
## lumen の場合は、serviceProviderの登録
sed -i '/EventServiceProvider/a $app->register(\\Takuya\\Laravel\\Backlog\\Providers\\AppServiceProvider::class);' bootstrap/app.php

## マイグレーション・モデル作成
php artisan backlog:make:migration:all
php artisan backlog:make:models:all

```

## 日付に関する注意点。

バックログのAPIが返却するデータはすべてUTCです。データ保存時はUTCで保存することを想定してます。laravel/lumenのタイムゾーン設定はUTCにして、表示時にタイムゾーンに合わせて表示するようにするほうが良いと思います。内部をJSTにすると９時間ズレます。
