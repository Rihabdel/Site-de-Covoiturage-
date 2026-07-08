FROM node:20-alpine

WORKDIR /app

# On copie les fichiers de configuration pour installer les dépendances
COPY package*.json ./
RUN npm install

# On copie le reste du code frontend
COPY . .

EXPOSE 5173