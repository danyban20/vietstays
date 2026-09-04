import fs from 'fs';
import path from 'path';
import { execFileSync } from 'child_process';
import { fileURLToPath } from 'url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const raw = fs.readFileSync(path.join(root, 'sftp-config.json'), 'utf8');
const host = raw.match(/"host"\s*:\s*"([^"]+)"/)?.[1];
const user = raw.match(/"user"\s*:\s*"([^"]+)"/)?.[1];
const password = raw.match(/"password"\s*:\s*"([^"]+)"/)?.[1];
const remoteBase = (raw.match(/"remote_path"\s*:\s*"([^"]+)"/)?.[1] || '/vietstays.com/public_html')
    .replace(/^\/+|\/+$/g, '');

const auth = `${user}:${password}`;
const cwd = `ftp://${host}/${remoteBase}/public`;

for (const dir of ['uploads']) {
    try {
        execFileSync('curl.exe', ['--user', auth, '--ftp-pasv', '-s', cwd, '-Q', `MKD ${dir}`], { stdio: 'inherit' });
        console.log(`Created ${dir} (or already exists)`);
    } catch {
        console.log(`Could not MKD ${dir} — may already exist`);
    }
}

try {
    execFileSync(
        'curl.exe',
        [
            '-T',
            path.join(root, 'public/uploads/.gitkeep'),
            `${cwd}/uploads/.gitkeep`,
            '--user',
            auth,
            '--ftp-pasv',
            '--silent',
            '--show-error',
        ],
        { stdio: 'inherit' },
    );
    console.log('Uploaded public/uploads/.gitkeep');
} catch (error) {
    console.error('Failed to upload .gitkeep:', error.message);
    process.exit(1);
}
