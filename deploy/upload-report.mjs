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

const file = 'public/vietstays-host-dashboard-progress.html';
const localPath = path.join(root, file);
const remotePath = `${remoteBase}/${file.replace(/\\/g, '/')}`;

execFileSync(
    'curl.exe',
    ['-T', localPath, `ftp://${host}/${remotePath}`, '--user', `${user}:${password}`, '--ftp-pasv', '--silent', '--show-error'],
    { stdio: 'inherit' },
);

console.log(`Uploaded ${file}`);
console.log('URL: https://vietstays.com/public/vietstays-host-dashboard-progress.html');
