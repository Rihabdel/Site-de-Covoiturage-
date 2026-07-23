FROM node:20-alpine

WORKDIR /app

# On copie les fichiers de configuration pour installer les dépendances
COPY package*.json ./
RUN npm install

# On copie le reste du code frontend
COPY . .
RUN npm run build

EXPOSE 5173
CMD ["npm", "run", "preview"]