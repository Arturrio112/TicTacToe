
const btn = document.getElementsByClassName("start")[0]
function makeBoard(board){
        const gameBoard = document.querySelector(".game-board")
        const banner = document.querySelector(".banner")
        gameBoard.innerHTML = ""
        banner.innerHTML = ""
        
        gameBoard.style.gridTemplateRows = `repeat(${board.length}, 1fr)`
        gameBoard.style.gridTemplateColumns = `repeat(${board[0].length}, 1fr)`
        boardLogic(board, gameBoard, banner)
}
function boardLogic(board, gameBoard, banner){
    for(let i=0; i< board.length; i++){
        for(let j=0; j<board[0].length; j++){
            let div = document.createElement("div")
            div.classList.add("game-tile")
            div.setAttribute("x",i)
            div.setAttribute("y",j)
            div.addEventListener("click", async()=>{
                
                if(board[i][j]!==" "){return}
                    const requestData = {
                        i: i,
                        j: j,
                      };
                    
                    try{
                        const res = await axios.put('http://tic.local:8080/pc',
                         JSON.stringify(requestData), {
                            headers: {
                              'Content-Type': 'application/json',
                            },
                          });
                        
                        console.log(res.data)
                        console.log(board)
                        if(banner.innerText==""){
                            div.innerText = res.data['sym']
                            board[i][j] = res.data['sym']
                            let coor = res.data['botCoor'];
                            if(coor[0]!=-1){
                                board[coor[0]][coor[1]]="X"
                                getDivByCordinates(coor[0], coor[1]).innerText = "X"
                            }
                            
                        }
                        if(res.data['win']&&banner.innerText==""&&res.data['botCoor'][0]==-1){
                            banner.innerText = `Player ${res.data['sym']} has won`
                            
                        }
                        if(res.data['win']&&banner.innerText==""&&res.data['botCoor'][0]!=-1){
                            banner.innerText = `Player X has won`
                            
                        }
                        if(res.data['full']&&banner.innerText==""){
                            banner.innerText = "It is a draw"
                        }
                        

                    }catch(e){
                        console.log(e)
                    }
                    
                
            })
            gameBoard.appendChild(div)
        }
    }
}
function getDivByCordinates(x ,y) {
    const div = document.querySelector(
      `[x="${x}"][y="${y}"]`
    );
    return div;
  } 
btn.addEventListener("click", async ()=>{
    const rows = document.getElementsByClassName("rows-count")[0].value
    const cols = document.getElementsByClassName("cols-count")[0].value
    const lineLength = document.getElementsByClassName("line-length")[0].value
    const formData = new FormData();
    formData.append('row', rows);
    formData.append('col', cols);
    formData.append('line', lineLength);
    
    try {
        const response = await axios.post('http://tic.local:8080/pc', formData,{
        headers: {
            'Content-Type': 'multipart/form-data'
        }});
        
        const board = response.data;
        console.log(board)
        makeBoard(board)
        
      } catch (error) {
        // Handle any errors
        console.error(error);
      }
   
    
    
})
const gameModeBtn = document.querySelectorAll(".button")
gameModeBtn.forEach((btn)=>{
    btn.addEventListener("click", ()=>{
        if(btn.innerText=="PC"){
            const modal = document.querySelector(".popup")
            modal.classList.add("hidden")
        }else{
            window.location.replace("http://tic.local:8080/multi")
        }
    })
})
