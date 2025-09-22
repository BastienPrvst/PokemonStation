require('dotenv').config();
const { createServer } = require("http");
const { Server } = require("socket.io");

const httpServer = createServer(function(req, res) {
});

const io = new Server(httpServer, {
    cors: {
        origin: ["https://pokemon-station.fr"]
    }
});

io.on('connection', (socket) => {

    socket.on('joinRoom', (roomName) => {
        if (!roomName) return;
        socket.join(roomName);
        socket.currentRoom = roomName;
    });

    socket.on('changePokemon', (pokemon) => {
        if (!socket.currentRoom) return;
        socket.to(socket.currentRoom).emit('changeOtherPokemon', pokemon);
    });

    socket.on('validatePokemon', (price) => {
        if (!socket.currentRoom) return;
        socket.to(socket.currentRoom).emit('validatePokemonFromOther', price);
    });

    socket.on('confirmedPokemon', () => {
        if (!socket.currentRoom) return;
        socket.to(socket.currentRoom).emit('confirmedPokemonFromOther');
    });

    socket.on('interested', (id) => {
        if (!socket.currentRoom) return;
        socket.to(socket.currentRoom).emit('interestedPokemonFromOther', id);
    });

    socket.on('successRedirect', () => {
        if (!socket.currentRoom) return;
        socket.to(socket.currentRoom).emit('successRedirectFromOther');
    })

});

httpServer.listen(3000,() => {
    console.log(`Serveur WebSocket en écoute sur le port ${process.env.PORT}`);
});