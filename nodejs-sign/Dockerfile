FROM node:16

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app
ADD . /usr/src/app

RUN yarn install

EXPOSE 3000

CMD ["yarn", "start:sign"]
