

const socket = io("http://tic.local:8081")

const btn = document.querySelector(".start")
const joinBtn = document.querySelector(".join")
let gameId = null;
socket.on("connect", ()=>{
    console.log("Connected to the server")
})
function makeBoard(board){
    const gameBoard = document.querySelector(".game-board")
    const banner = document.querySelector(".banner")
    gameBoard.innerHTML = ""
    banner.innerHTML = ""
    gameBoard.style.gridTemplateRows = `repeat(${board.length}, 1fr)`
    gameBoard.style.gridTemplateColumns = `repeat(${board[0].length}, 1fr)`
    for(let i=0; i< board.length; i++){
        for(let j=0; j<board[0].length; j++){
            let div = document.createElement("div")
            div.classList.add("game-tile")
            div.setAttribute("x",i)
            div.setAttribute("y",j)
            div.addEventListener("click",()=>{
                if(board[i][j]!==" "){
                    return
                }
                const requestData = {
                    i: i,
                    j: j,
                };
            })
        }
    }
}
btn.addEventListener("click", ()=>{
    const rows = document.getElementsByClassName("rows-count")[0].value
    const cols = document.getElementsByClassName("cols-count")[0].value
    const lineLength = document.getElementsByClassName("line-length")[0].value
    const formData = new FormData();
    formData.append('row', rows);
    formData.append('col', cols);
    formData.append('line', lineLength);
    let id = Math.random();
    gameId = id;
    const data = {
        action: 'start',
        gameId: id.toString(),
        row: rows,
        col: cols,
        line: lineLength
    }
    socket.emit('message', JSON.stringify(data))
    socket.on("board", (board)=>{
        makeBoard(board)
    })

})
joinBtn.addEventListener("click", ()=>{
    if(gameId){
        const data = {
            action: 'join',
            gameId: gameId
        }
        socket.emit('message', JSON.stringify(data))
    }
})