# 文化祭ランキング — GCP (Cloud Run + Cloud SQL) デプロイ手順

## 1. 事前準備

```bash
gcloud auth login
gcloud config set project <YOUR_PROJECT_ID>
gcloud services enable run.googleapis.com sqladmin.googleapis.com \
    cloudbuild.googleapis.com artifactregistry.googleapis.com
```

## 2. Cloud SQL (MySQL) を作成

```bash
# インスタンス作成（db-f1-micro = 最小 / 一番安い構成）
gcloud sql instances create festival-sql \
    --database-version=MYSQL_8_0 \
    --tier=db-f1-micro \
    --region=asia-northeast1 \
    --root-password='<ROOT_PASS>'

# DB & ユーザー
gcloud sql databases create festival --instance=festival-sql
gcloud sql users create festival \
    --instance=festival-sql \
    --password='<APP_PASS>'

# 接続名を控える（例: my-project:asia-northeast1:festival-sql）
gcloud sql instances describe festival-sql --format='value(connectionName)'
```

## 3. イメージをビルドして Cloud Run にデプロイ

```bash
# APP_KEY を手元で生成（.env の APP_KEY= 行をコピーして使う）
php artisan key:generate --show

INSTANCE_CONN=<上で控えた接続名>
APP_KEY=<生成した base64:... キー>

gcloud run deploy festival \
    --source . \
    --region=asia-northeast1 \
    --allow-unauthenticated \
    --add-cloudsql-instances=$INSTANCE_CONN \
    --set-env-vars=APP_ENV=production,APP_DEBUG=false,APP_URL=https://<自動発行URL>,LOG_CHANNEL=stderr \
    --set-env-vars=DB_CONNECTION=mysql,DB_SOCKET=/cloudsql/$INSTANCE_CONN,DB_DATABASE=festival,DB_USERNAME=festival,DB_PASSWORD='<APP_PASS>' \
    --set-env-vars=APP_KEY="$APP_KEY"
```

初回デプロイ後に Cloud Run が払い出す URL を `APP_URL` に再設定して再デプロイすると、生成されるリンクが正しくなります。

## 4. マイグレーション & シード（初回のみ）

Cloud Run の 1 回限りのジョブで実行:

```bash
gcloud run jobs create festival-migrate \
    --image=<Cloud Run がビルドしたイメージ URL> \
    --region=asia-northeast1 \
    --add-cloudsql-instances=$INSTANCE_CONN \
    --set-env-vars=DB_CONNECTION=mysql,DB_SOCKET=/cloudsql/$INSTANCE_CONN,DB_DATABASE=festival,DB_USERNAME=festival,DB_PASSWORD='<APP_PASS>',APP_KEY="$APP_KEY" \
    --command=php --args=artisan,migrate,--seed,--force

gcloud run jobs execute festival-migrate --region=asia-northeast1 --wait
```

`<Cloud Run がビルドしたイメージ URL>` は `gcloud run services describe festival --region=asia-northeast1 --format='value(spec.template.spec.containers[0].image)'` で取得できます。

## 5. 動作確認

- `https://<Cloud Run URL>/`                 → 総合ランキング
- `https://<Cloud Run URL>/competitions/{id}` → 競技別ランキング
- `https://<Cloud Run URL>/scores/create`     → 点数入力

認証なしの構成なので、入力画面の URL はシステム担当者だけで共有してください。
