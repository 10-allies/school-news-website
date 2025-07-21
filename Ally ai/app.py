from flask import Flask,request,render_template
import requests
app=Flask(__name__)
API_KEY="AIzaSyBjFtaHem1lbLt7o7tX1-pf4spnD6j7nAo"
API_URL=f"https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash-001:generateContent?key={API_KEY}"
@app.route("/app")
def index():
    question= request.args.get("question","")
    answer=""
    if question:
        payload={
            "contents":[
                {
                    "parts":[
                        {
                            "text":question
                        }
                    ]
                }
            ]
        }
        response=requests.post(API_URL,json=payload,headers={"content-type":"application/json"})
        if response.ok:
            data=response.json()
            try:
                answer=data['candidates'][0]['content']['parts'][0]['text']
            except (KeyError, IndexError):
                answer="sorry Ally ai is still under development by the Alliance Team"
        else:
            answer=f"API Error: {response.status_code} {response.text}"
    return render_template("response.html",  answer=answer)
if __name__=="__main__":
    app.run(host="192.168.1.77",port=8000,debug=True)

                  