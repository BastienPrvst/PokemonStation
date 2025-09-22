FROM node:22.17

WORKDIR /usr/src/app

COPY ../../node/package*.json ./
RUN npm install

EXPOSE 4000
CMD ["sh", "-c", "npm install && node index.js"]