import { spawn, type Subprocess } from "bun";
import { watch } from "fs";

const PHP_PORT = 8000;
const BROWSER_SYNC_PORT = 3000;

let phpServer: Subprocess | null = null;
let browserSync: Subprocess | null = null;
let tailwindWatch: Subprocess | null = null;

async function startPhpServer() {
  console.log(`Starting PHP server on port ${PHP_PORT}...`);
  phpServer = spawn({
    cmd: ["php", "-S", `localhost:${PHP_PORT}`, "-t", "public"],
    stdout: "inherit",
    stderr: "inherit",
  });
}

async function startTailwindWatch() {
  console.log("Starting Tailwind CSS watcher...");
  tailwindWatch = spawn({
    cmd: [
      "bunx",
      "@tailwindcss/cli",
      "-i",
      "resources/css/app.css",
      "-o",
      "public/assets/css/app.css",
      "--watch",
    ],
    stdout: "inherit",
    stderr: "inherit",
  });
}

async function buildTypeScript() {
  console.log("Building TypeScript...");
  const result = spawn({
    cmd: [
      "bun",
      "build",
      "resources/ts/app.ts",
      "--outdir",
      "public/assets/js",
      "--minify",
    ],
    stdout: "inherit",
    stderr: "inherit",
  });
  await result.exited;
}

async function startBrowserSync() {
  console.log(`Starting BrowserSync on port ${BROWSER_SYNC_PORT}...`);
  browserSync = spawn({
    cmd: [
      "bunx",
      "browser-sync",
      "start",
      "--proxy",
      `localhost:${PHP_PORT}`,
      "--port",
      String(BROWSER_SYNC_PORT),
      "--files",
      "public/assets/css/*.css",
      "--files",
      "public/assets/js/*.js",
      "--files",
      "src/**/*.php",
      "--no-open",
      "--no-notify",
    ],
    stdout: "inherit",
    stderr: "inherit",
  });
}

function watchTypeScript() {
  console.log("Watching TypeScript files...");
  const watcher = watch(
    "resources/ts",
    { recursive: true },
    async (event, filename) => {
      if (filename?.endsWith(".ts")) {
        console.log(`TypeScript file changed: ${filename}`);
        await buildTypeScript();
      }
    }
  );

  return watcher;
}

async function cleanup() {
  console.log("\nShutting down...");

  if (phpServer) {
    phpServer.kill();
  }
  if (browserSync) {
    browserSync.kill();
  }
  if (tailwindWatch) {
    tailwindWatch.kill();
  }

  process.exit(0);
}

process.on("SIGINT", cleanup);
process.on("SIGTERM", cleanup);

async function main() {
  console.log("FastClient Development Server");
  console.log("=============================\n");

  // Initial builds
  await buildTypeScript();

  // Start all services
  await startPhpServer();
  await startTailwindWatch();
  await startBrowserSync();

  // Watch TypeScript
  const tsWatcher = watchTypeScript();

  console.log("\n=============================");
  console.log(`PHP Server: http://localhost:${PHP_PORT}`);
  console.log(`BrowserSync: http://localhost:${BROWSER_SYNC_PORT}`);
  console.log("=============================\n");
  console.log("Press Ctrl+C to stop\n");

  // Keep the process alive
  await new Promise(() => {});
}

main().catch(console.error);
