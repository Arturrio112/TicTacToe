let playerSym;
let currSym="O";
let boardCheck;
function makeBoard(board){
  const gameBoard = document.querySelector(".game-board")
  const banner = document.querySelector(".banner")
  gameBoard.innerHTML = ""
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
            div.innerText = board[i][j]
            div.addEventListener("click", async()=>{
                
                if(board[i][j]!==" "||playerSym!==currSym||banner.innerText!=""){return}
                    const requestData = {
                        i: i,
                        j: j,
                      };
                    try{
                        const res = await axios.put('http://tic.local:8080/multi',
                         JSON.stringify(requestData), {
                            headers: {
                              'Content-Type': 'application/json',
                            },
                          });
                          div.innerText = playerSym
                          board[i][j] = playerSym
                          if(res.data['win']){
                            makeBanner(true)
                          }
                          if(res.data['full']){
                            makeBanner(false);
                          }
                          currSym  = currSym=="O"?"X":"O";
                          waitForBoardUpdate()
                    }catch(e){
                        console.log(e)
                    }
            })
            gameBoard.appendChild(div)
        }
    }
}
function makeBanner(isWin){
  const banner = document.querySelector(".banner")
  if(isWin){
    banner.innerText = `Player ${currSym} has won`
    
  }else{
    banner.innerText = "It is a draw"
  }
}
function getDivByCordinates(x ,y) {
    const div = document.querySelector(
      `[x="${x}"][y="${y}"]`
    );
    return div;
  } 
  function areTheSame(arr1, arr2){
    for(let i = 0; i < arr1.length; i++){
      for(let j = 0; j < arr1[i].length; j++){
        if(arr1[i][j] !== arr2[i][j]){
          return false;
        }
      }
    }
    return true;
  }
  
async function waitForBoardUpdate(){
  let flag = false
  while(!flag){
    const requestData = {
      check: true,
    };
    try{
      const res = await axios.put('http://tic.local:8080/multi',
        JSON.stringify(requestData), {
          headers: {
            'Content-Type': 'application/json',
          },
        });
      //console.log(res.data['board'])
      //console.log(boardCheck)
      if(!areTheSame(res.data['board'], boardCheck)){
        console.log(true)
        if(res.data['over']==true&&res.data['full']==true){
          makeBanner(false);
        }
        if(res.data['over']==true){
          makeBanner(true);
        }
        currSym  = currSym=="O"?"X":"O";
        boardCheck = res.data['board']
        makeBoard(res.data['board'])
        flag=true
      }
    }catch(e){
      console.error(e);
    }
  }
}

document.querySelector(".start").addEventListener("click", async function() {
    console.log("pressed")
    const rows = document.querySelector(".rows-count").value
    const cols = document.querySelector(".cols-count").value
    const lineLength = document.querySelector(".line-length").value
    const formData = new FormData();
    formData.append('row', rows);
    formData.append('col', cols);
    formData.append('line', lineLength);
    formData.append('action', "start");
    document.querySelector(".banner").innerHTML = ""
    try{
      const response = await axios.post('http://tic.local:8080/multi', formData,{
        headers: {
            'Content-Type': 'multipart/form-data'
        }});
      document.querySelector(".join").disabled = true
      const board = response.data
      boardCheck = board;
      makeBoard(board);
      playerSym= "O";
    }catch(e){
      console.error(e);
    }
    
})
document.querySelector(".join").addEventListener("click", async function(){
  document.querySelector(".start").disabled = true
  const formData = new FormData();
  formData.append('action', "join");
  document.querySelector(".banner").innerHTML = ""
  try{
    const response = await axios.post('http://tic.local:8080/multi', formData,{
      headers: {
        'Content-Type': 'multipart/form-data'
    }});
    playerSym= "X";
    const board = response.data
    boardCheck=board
    makeBoard(board);
    waitForBoardUpdate();
  }catch(e){
    console.log(e)
  }
})