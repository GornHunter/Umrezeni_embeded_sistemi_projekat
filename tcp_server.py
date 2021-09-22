import socket
import mysql.connector
import json
import datetime

mydb = mysql.connector.connect(
    host = "localhost",
    user = "karadzic.e195",
    password = "L2@zskEx",
    database = "db_karadzic_e195"
)

HOST = ''           # Symbolic name meaning all available interfaces
PORT = 50051        # Arbitrary non-privileged port
NUM_OF_CLIENTS = 1

tcpSocket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
tcpSocket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
tcpSocket.bind((HOST, PORT))
tcpSocket.listen(NUM_OF_CLIENTS)

print ('Echo server is ready to receive (port ' + str(PORT) + ')\n')

msgCnt = 1

while True:
    try:
        conn, addr = tcpSocket.accept()
        print ('Connected by', addr)
        while True:
            data_in = conn.recv(1024)
            if not data_in:
                break
            
            
            try:
                data = json.loads(data_in.decode("utf-8").split("\n")[5])
                error = 0
            except:
                error = -1
                

            if error == 0:
                if ('temperatura' in data) and ('vlaznost' in data) and ('pritisak' in data) and ('osvetljenje' in data):
                    vreme = datetime.datetime.now().strftime("%d-%m-%Y %H:%M:%S")
                    
                    print (' Vreme: ', vreme)
                    print (' Temperatura: ', data["temperatura"], "\u00b0C")
                    print (' Vlaznost: ', data["vlaznost"], "mBar")
                    print (' Pritisak: ', data["pritisak"], "%")
                    print (' Osvetljenje: ', data["osvetljenje"], "lux")
            
                    mycursor = mydb.cursor()
                    mycursor.execute("INSERT INTO podaci (temperatura, vlaznost, pritisak, osvetljenje) VALUES (" + str(data["temperatura"]) + "," + str(data["vlaznost"]) + "," + str(data["pritisak"]) + "," + str(data["osvetljenje"]) + ")")
                    mydb.commit()

                    conn.sendall(bytearray("HTTP/1.1 200 OK\r\nContent-Type: text/*\r\n\r\nOK", "utf-8"))
                else:
                    print (' Bad request!')
                    conn.sendall(bytearray("HTTP/1.1 400 Bad Request\r\nContent-Type: text/*\r\n\r\nBad Request", "utf-8"))
            else:
                    print (' Invalid JSON!')
                    conn.sendall(bytearray("HTTP/1.1 422 Unprocessable Entity\r\nContent-Type: text/*\r\n\r\nUnprocessable Entity", "utf-8"))
    except:
        conn.close()
        print('ERROR in Echo TCP')