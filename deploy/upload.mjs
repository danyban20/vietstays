import fs from 'fs';
import path from 'path';
import { execFileSync } from 'child_process';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const raw = fs.readFileSync(path.join(root, 'sftp-config.json'), 'utf8');

const host = raw.match(/"host"\s*:\s*"([^"]+)"/)?.[1];
const user = raw.match(/"user"\s*:\s*"([^"]+)"/)?.[1];
const password = raw.match(/"password"\s*:\s*"([^"]+)"/)?.[1];
const remoteBase = (raw.match(/"remote_path"\s*:\s*"([^"]+)"/)?.[1] || '/vietstays.com/public_html')
    .replace(/^\/+|\/+$/g, '');

if (!host || !user || !password) {
    console.error('Missing FTP credentials in sftp-config.json');
    process.exit(1);
}

const localFiles = [
    'app/Http/Controllers/Api/ApartmentController.php',
    'app/Http/Controllers/Api/BookingController.php',
    'app/Http/Controllers/Api/CustomerController.php',
    'app/Http/Controllers/Api/TeamController.php',
    'app/Http/Controllers/Api/DashboardController.php',
    'app/Http/Controllers/PublicStorageController.php',
    'app/Models/HostCustomer.php',
    'app/Models/HostTeamMember.php',
    'app/Models/HostTeamInvitation.php',
    'app/Services/ApartmentCreationService.php',
    'app/Services/CustomerAggregationService.php',
    'app/Services/TeamService.php',
    'config/filesystems.php',
    'routes/api.php',
    'routes/web.php',
    'public/uploads/.gitkeep',
];

function uploadFile(localRelative) {
    const localPath = path.join(root, localRelative);
    const remotePath = `${remoteBase}/${localRelative.replace(/\\/g, '/')}`;
    const url = `ftp://${host}/${remotePath}`;

    execFileSync(
        'curl.exe',
        ['-T', localPath, url, '--user', `${user}:${password}`, '--ftp-pasv', '--silent', '--show-error'],
        { stdio: 'inherit' },
    );

    console.log(`Uploaded ${localRelative} -> ftp://${host}/${remotePath}`);
}

const phpOnly = process.argv.includes('--php-only');

let count = 0;

function walkDir(localDir, remoteDir) {
    const absDir = path.join(root, localDir);
    for (const entry of fs.readdirSync(absDir, { withFileTypes: true })) {
        const localRelative = path.join(localDir, entry.name).replace(/\\/g, '/');
        const remotePath = `${remoteDir}/${entry.name}`.replace(/\\/g, '/');

        if (entry.isDirectory()) {
            walkDir(localRelative, remotePath);
            continue;
        }

        uploadFile(localRelative);
        count += 1;
    }
}

for (const file of localFiles) {
    uploadFile(file);
    count += 1;
}

if (!phpOnly) {
    walkDir('public/build', 'public/build');
}

console.log(`Deploy upload complete. Files uploaded: ${count}`);

// Drop cached config so new filesystem disks (e.g. uploads) are picked up.
for (const cacheFile of ['bootstrap/cache/config.php', 'bootstrap/cache/routes-v7.php']) {
    try {
        execFileSync(
            'curl.exe',
            ['--user', `${user}:${password}`, '--ftp-pasv', '--silent', '--show-error', `ftp://${host}/${remoteBase}/${cacheFile}`, '-Q', `DELE ${cacheFile}`],
            { stdio: 'ignore' },
        );
        console.log(`Cleared remote cache file: ${cacheFile}`);
    } catch {
        // Cache file may not exist.
    }
}

// Ensure uploads directory exists on server
try {
    const uploadsUrl = `ftp://${host}/${remoteBase}/public/uploads`;
    execFileSync('curl.exe', ['--user', `${user}:${password}`, '--ftp-pasv', '--silent', '--show-error', uploadsUrl, '-Q', 'MKD public/uploads'], { stdio: 'ignore' });
} catch {
    // Directory may already exist.
}
