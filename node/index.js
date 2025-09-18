require('dotenv').config();
const { createServer } = require("http");
const { Server } = require("socket.io");

const httpServer = createServer(function(req, res) {
    console.log('skibidi');
});

const io = new Server(httpServer, {
    cors: {
        origin: ["http://127.0.0.1:8000", "https://localhost"]
    }
});

io.sockets.on('connection', (socket) => {

    socket.on('joinRoom', (room) => socket.join(room) );

    socket.on('changePokemon', (pokemon) => {
        console.log(pokemon)
        socket.to('tradeRoom').emit('changeOtherPokemon', pokemon);
    })

    socket.on('validatePokemon', (price) => {
        socket.to('tradeRoom').emit('validatePokemonFromOther', price);
    });

    socket.on('confirmedPokemon', () => {
        socket.to('tradeRoom').emit('confirmedPokemonFromOther');
    })

    socket.on('interested', (id) =>{
        socket.to('tradeRoom').emit('interestedPokemonFromOther', id);
    })
})

httpServer.listen(4000,() => {
    console.log(`Serveur WebSocket en écoute sur le port ${process.env.PORT}`);
});