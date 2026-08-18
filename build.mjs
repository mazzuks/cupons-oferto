import { cp, mkdir, readFile, rm, writeFile } from "node:fs/promises";

await rm("dist", { recursive: true, force: true });
await mkdir("dist/data", { recursive: true });
await mkdir("dist/.openai", { recursive: true });
await mkdir("dist/server", { recursive: true });

await Promise.all([
  cp("index.html", "dist/index.html"),
  cp("admin-lite.html", "dist/admin-lite.html"),
  cp("styles.css", "dist/styles.css"),
  cp("app.js", "dist/app.js"),
  cp("data/cupons.js", "dist/data/cupons.js"),
  cp(".openai/hosting.json", "dist/.openai/hosting.json")
]);

const staticFiles = [
  ["index.html", "text/html; charset=utf-8"],
  ["admin-lite.html", "text/html; charset=utf-8"],
  ["styles.css", "text/css; charset=utf-8"],
  ["app.js", "text/javascript; charset=utf-8"],
  ["data/cupons.js", "text/javascript; charset=utf-8"]
];

const assets = Object.fromEntries(
  await Promise.all(
    staticFiles.map(async ([file, contentType]) => [
      `/${file}`,
      {
        contentType,
        body: await readFile(file, "utf8")
      }
    ])
  )
);

const server = `const assets = ${JSON.stringify(assets)};

function normalizePath(pathname) {
  if (pathname === "/" || pathname === "") return "/index.html";
  return pathname.endsWith("/") ? pathname + "index.html" : pathname;
}

export default {
  async fetch(request) {
    const url = new URL(request.url);
    const pathname = normalizePath(decodeURIComponent(url.pathname));
    const asset = assets[pathname];

    if (!asset) {
      return new Response("Not found", { status: 404 });
    }

    return new Response(asset.body, {
      headers: {
        "Content-Type": asset.contentType,
        "Cache-Control": "public, max-age=60"
      }
    });
  }
};
`;

await writeFile("dist/server/index.js", server);
