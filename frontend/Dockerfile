FROM node:20-alpine

RUN apk add --no-cache wget curl

WORKDIR /app

COPY package*.json ./

RUN npm ci --only=production=false

COPY . .

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]