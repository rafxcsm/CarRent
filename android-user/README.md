# Drivora Android App

`Drivora.apk` is the customer-only Android app. The current local Wi-Fi build connects to the Laravel server on this PC. A public build connects to the deployed HTTPS site and works away from the PC's Wi-Fi.

## Install and use

1. Transfer `Drivora.apk` to the phone (for example, with a cloud drive or Nearby Share) and install it. Allow installs from that source if Android prompts you.
2. For a local Wi-Fi build only, connect the phone and PC to the same Wi-Fi network.
3. For a local Wi-Fi build only, from the project root start Laravel and leave the terminal open:

   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```

The existing local APK is configured to use the PC's Wi-Fi address. It requires that address, the local database, and the Laravel server to remain available. After the Render deployment is live, copy `.env.mobile.example` to `.env.mobile`, replace the sample host with the Render HTTPS URL, and rebuild the customer APK. That public build will use the hosted backend from any network.

If the phone cannot reach the PC on a local Wi-Fi build, run this once in **PowerShell as Administrator** to allow devices on the local Wi-Fi subnet to connect to the Laravel port:

```powershell
New-NetFirewallRule -DisplayName 'Drivora Wi-Fi API (TCP 8000)' -Direction Inbound -Action Allow -Protocol TCP -LocalPort 8000 -RemoteAddress '192.168.1.0/24' -Profile Public
```

## Rebuild the Wi-Fi APK

From the project root:

```powershell
npm.cmd run build:android-user
npx.cmd cap sync android
# JDK 21 and the Android SDK must be installed/configured.
Push-Location android
.\gradlew.bat assembleDebug
Pop-Location
Copy-Item .\android\app\build\outputs\apk\debug\app-debug.apk .\android-user\Drivora.apk -Force
```

The generated APK will be at `android-user\Drivora.apk`.
