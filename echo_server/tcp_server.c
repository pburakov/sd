#include <stdio.h>
#include <stdlib.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <sys/socket.h>

#define DEFAULT_PORT 8080
#define BACKLOG 128
#define BUFFER_SIZE 1024

void error(char *msg) {
    perror(msg);
    exit(1);
}

int main(int argc, char *argv[]) {
    int port = argc < 2 ? DEFAULT_PORT : atoi(argv[1]);

    int server_fd, client_fd;
    struct sockaddr_in server, client;
    char buffer[BUFFER_SIZE];

    if ((server_fd = socket(AF_INET, SOCK_STREAM, 0)) < 0)
        error("Socket creation error");

    server.sin_family = AF_INET;
    server.sin_port = htons(port);
    server.sin_addr.s_addr = htonl(INADDR_ANY);

    if (bind(server_fd, (struct sockaddr *) &server, sizeof(server)) < 0)
        error("Socket bind error");

    if (listen(server_fd, BACKLOG) < 0)
        error("Error entering passive mode");

    printf("Server started. Listening on port %d\n", port);

    for (;;) {
        socklen_t client_len = sizeof(client);
        if ((client_fd = accept(server_fd, (struct sockaddr *) &client, &client_len)) < 0)
            error("Error accepting connection");

        printf("Client connected: %s\n", inet_ntoa(client.sin_addr));

        for (;;) {
            ssize_t msglen;
            if ((msglen = recv(client_fd, buffer, BUFFER_SIZE, 0)) < 0)
                error("Error reading from client");

            if (!msglen) break;

            if (send(client_fd, buffer, msglen, 0) < 0)
                error("Error sending to client");
        }
    }
}