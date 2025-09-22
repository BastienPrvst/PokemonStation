require('dotenv').config();
const { createServer } = require("http");
const { Server } = require("socket.io");

const httpServer = createServer(function(req, res) {
});

const io = new Server(httpServer, {
    cors: {
        origin: ["http://127.0.0.1:8000", "https://localhost"]
    }
});

io.on('connection', (socket) => {

    socket.on('joinRoom', (roomName) => {
        if (!roomName) return;
        socket.join(roomName);
        socket.currentRoom = roomName;
        console.log(`${socket.id} a rejoint la room ${roomName}`);
    });

    socket.on('changePokemon', (pokemon) => {
        if (!socket.currentRoom) return;
        console.log('changePokemon reçu dans', socket.currentRoom, pokemon);
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

httpServer.listen(4000,() => {
    console.log(`Serveur WebSocket en écoute sur le port ${process.env.PORT}`);
});