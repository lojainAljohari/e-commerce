FROM node:22-bookworm-slim
WORKDIR /app
COPY package*.json ./
RUN ls -la; wc -c package.json package-lock.json; node -v; npm -v; npm ci 2>&1 | tail -15; echo "modules: $(ls node_modules 2>/dev/null | wc -l)"
COPY . .
RUN ls -la; ls -la src; npm run build 2>&1 | tail -30; ls -la dist
CMD ["node", "dist/main.js"]
