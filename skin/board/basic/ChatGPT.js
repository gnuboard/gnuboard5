const OPENAI_API_KEY = 'OPENAI_API_KEY'  // OPENAI_API_KEY 삽입

function Send () {
  const MeQ = txtMsg.value
  if (MeQ == '') {
    alert('질문을 입력하세요!') // 
    txtMsg.focus()
    return
  }

  const aiHttp = new XMLHttpRequest()
  aiHttp.open('POST', 'https://api.openai.com/v1/completions')
  aiHttp.setRequestHeader('Accept', 'application/json')
  aiHttp.setRequestHeader('Content-Type', 'application/json')
  aiHttp.setRequestHeader('Authorization', 'Bearer ' + OPENAI_API_KEY)

  aiHttp.onreadystatechange = function () {
    if (aiHttp.readyState === 4) {
      // console.log(aiHttp.status);
      let aiJson = {}
      if (wr_content.value != '') wr_content.value += '\n\n'

      try {
        aiJson = JSON.parse(aiHttp.responseText)
      } catch (Ext) {
        wr_content.value += 'Error: ' + Ext.message
      }

      if (aiJson.error && aiJson.error.message) {
        wr_content.value += 'Error: ' + aiJson.error.message
      } else if (aiJson.choices && aiJson.choices[0].text) {
        let s = aiJson.choices[0].text

        if (selLang.value != 'ko-KR') {
          const a = s.split('')
          if (a.length == 2) {
            s = a[1]
          }
        }

        if (s == '') s = '응답이 없습니다!'
        wr_content.value += s
      }
    }
  }

  const aiModel = selModel.value// 적용 AI모델 "text-davinci-003";
  const aiMaxTokens = 2048
  const aiUserId = '1'

  const data = {
    model: aiModel,
    prompt: MeQ,
    max_tokens: aiMaxTokens,
    user: aiUserId,
  }

  aiHttp.send(JSON.stringify(data))

  if (wr_content.value != '') wr_content.value += '\n\n'// 한 개의 대화가 끝나는 지점 개행
  wr_content.value += '질문: ' + MeQ
  txtMsg.value = ''
}
