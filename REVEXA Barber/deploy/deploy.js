const ftp = require("basic-ftp");
const fs = require("fs");
const path = require("path");
const { exec } = require("child_process");
require("dotenv").config();

const client = new ftp.Client();
client.ftp.verbose = true;

async function buildFlutter() {
    console.log("🔨 Building Flutter Web App...");
    return new Promise((resolve, reject) => {
        exec("flutter build web --release --base-href /revexa_sistemas/Sistemas/Revexa_Barber/", { cwd: path.join(__dirname, "..") }, (error, stdout, stderr) => {
            if (error) {
                console.error(`Build error: ${error.message}`);
                return reject(error);
            }
            if (stderr) console.log(stderr);
            console.log(stdout);
            console.log("✅ Build completed successfully.");
            resolve();
        });
    });
}

async function deploy() {
    const host = process.env.FTP_HOST;
    const user = process.env.FTP_USER;
    const password = process.env.FTP_PASSWORD;
    const remoteRoot = process.env.FTP_REMOTE_ROOT;

    if (!host || !user || !password || !remoteRoot) {
        console.error("❌ Error: Please configure deploy/.env file with your FTP credentials.");
        process.exit(1);
    }

    try {
        await buildFlutter();

        console.log("🚀 Connecting to FTP...");
        await client.access({
            host: host,
            user: user,
            password: password,
            secure: false // Set to true if using FTPS
        });

        console.log("📂 Uploading API...");
        await client.uploadFrom(path.join(__dirname, "../backend/api.php"), remoteRoot + "api.php");

        console.log("📂 Uploading Web App...");
        await client.ensureDir(remoteRoot);
        await client.clearWorkingDir(); // Optional: Clear directory before upload
        await client.uploadFromDir(path.join(__dirname, "../build/web"), remoteRoot);

        console.log("✨ Deployment successful!");
    } catch (err) {
        console.log("❌ Deployment failed:", err);
    }
    client.close();
}

deploy();
