# Drivora free deployment (Render + Supabase)

This setup uses one Render Free Docker web service for the Laravel website and API, and one Supabase Free project for PostgreSQL and file storage. It does not create a paid Render database, disk, or cron service.

## Before publishing

- The current Drivora Supabase project is already set up, and the existing MySQL records and 76 media files have been copied and verified there. A private MySQL backup was also created. Keep that backup outside GitHub and retain it until the deployed app has been checked.
- The Render service has not been created or deployed yet. Its startup script applies any pending Laravel migrations; migrations alone do not transfer data from MySQL. If you use a different Supabase project, repeat the backup, database import, and media copy before deploying.
- The repo must contain the intended app changes and this deployment setup. Do not commit `.env`, `.env.mobile`, database dumps, payment proofs, or credentials.
- Make sure the Supabase project is in a region close to your customers. Free projects can pause after a week with too little database activity.

## Create Supabase resources

For this deployment, continue using the existing Drivora project in Singapore. It already has the public `vehicle-images` bucket and private `rental-proofs` bucket, and the files have been copied. For a fresh Supabase project:

1. Create a Supabase Free project in a region near your customers and save its database password privately.
2. In **Storage**, create a public bucket named `vehicle-images` and a private bucket named `rental-proofs`.
3. Allow the needed image types in `vehicle-images`. Keep `rental-proofs` private and set its file limit to at least 10 MB.
4. From **Project Settings → API Keys**, copy the project URL and a server-side Supabase Secret key (`sb_secret_...`). A legacy `service_role` key is also accepted. Keep either server credential out of Vue code, the APK, and GitHub; the environment variable retains the compatibility name `SUPABASE_SERVICE_ROLE_KEY`.
5. From **Connect → Session pooler**, copy the PostgreSQL connection URL. Use the session-pooler host and port 5432. Keep the password private; if it contains URL-reserved characters, URL-encode them in the connection URL.

## Create the Render service

1. Push the reviewed project changes to the GitHub branch you want to deploy. Render can deploy only code that has been pushed to GitHub.
2. In Render, create a **Blueprint** from that GitHub repository and use `render.yaml`.
3. Confirm the only service is `drivora-web` on the **Free** plan. Do not add a paid database, disk, or cron job.
4. In the Render service's Environment page, fill in the values requested by the blueprint:
   - `APP_KEY`: run `php artisan key:generate --show` locally and paste the result into Render. Do not commit it.
   - `DB_URL`: the Supabase session-pooler URL.
   - `SUPABASE_URL`: the Supabase project URL.
   - `SUPABASE_SERVICE_ROLE_KEY`: the server-side Supabase Secret key (`sb_secret_...`) or legacy `service_role` key.
5. Deploy. The container builds Vue assets, then runs `php artisan migrate --force` at startup. Check Render's deploy logs and open the Render URL followed by `/up`; the health check should return `OK`.
6. For the current Drivora project, the rental records and media are already in Supabase. The legacy session rows were not migrated, so users need to sign in again after deployment. Confirm that a platform super admin exists. If not, run `php artisan super-admin:create` locally while temporarily setting `DB_CONNECTION=pgsql`, `DB_SSLMODE=require`, and `DB_URL` to the Supabase connection URL in that PowerShell session. Clear those session variables immediately afterward. This command prompts for the admin password locally.
7. For a fresh Supabase project, first import the backed-up MySQL records, then set the Supabase variables in the local `.env` (never commit it) and run `php artisan supabase:sync-legacy-uploads --dry-run`. If the file list is correct, run `php artisan supabase:sync-legacy-uploads`. It copies vehicle photos to the public vehicle bucket and legacy proof files to the private bucket, leaving local files in place.

## Build the public Drivora APK

After the site is live, copy `.env.mobile.example` to `.env.mobile` (use `Copy-Item .env.mobile.example .env.mobile -Force` in PowerShell to replace the existing local-Wi-Fi settings), replace the example hostname in both settings with the Render HTTPS URL, then run from the project root:

```powershell
npm install
npm run build:android-user
npx cap sync android
Push-Location android
.\gradlew.bat assembleDebug
Pop-Location
```

The APK is generated at `android\app\build\outputs\apk\debug\app-debug.apk`. This is a debug APK for direct installation. It reaches the deployed HTTPS API over any internet connection, so the phone no longer needs the laptop's Wi-Fi.

## Free-plan limits

Render Free services sleep after 15 minutes without incoming traffic and may take about a minute to wake. Their local filesystem is temporary, so uploads must use Supabase Storage. Supabase Free currently includes 500 MB database storage and 1 GB file storage, and can pause projects after a week with too little activity. Free tiers can change; monitor usage and keep a separate backup of business data.

The deployment keeps `MAIL_MAILER=log`. Subscription reminder emails are not delivered yet, and Render Cron Jobs have a $1 monthly minimum. Email delivery and a free external scheduler need separate setup before those reminders work in production.

To avoid any chance of metered Render charges, do not add a payment method. Render says it suspends free services or disables new builds when included usage is exceeded and no payment method is on file.
