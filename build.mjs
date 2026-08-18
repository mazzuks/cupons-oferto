import { cp, mkdir, rm } from "node:fs/promises";

await rm("dist", { recursive: true, force: true });
await mkdir("dist/data", { recursive: true });

await Promise.all([
  cp("index.html", "dist/index.html"),
  cp("admin-lite.html", "dist/admin-lite.html"),
  cp("styles.css", "dist/styles.css"),
  cp("app.js", "dist/app.js"),
  cp("data/cupons.js", "dist/data/cupons.js")
]);
