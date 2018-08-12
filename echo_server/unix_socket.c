#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/socket.h>
#include <sys/un.h>
#include <unistd.h>

#define DEFAULT_PATH "tmp.sock"
#define BACKLOG 128
#define BUFFER_SIZE 1024

void error(char *msg) {
    perror(msg);
    exit(1);
}

int main(int argc, char *argv[]) {
    char *path = argc < 2 ? DEFAULT_PATH : argv[1];

    int server_fd, client_fd;
    struct sockaddr_un server, client;
    char buffer[BUFFER_SIZE];

    if ((server_fd = socket(AF_UNIX, SOCK_STREAM, 0)) < 0)
        error("Socket creation error");

    server.sun_family = AF_UNIX;
    strncpy(server.sun_path, path, strlen(path) + 1);
    unlink(server.sun_path);

    if (bind(server_fd, (struct sockaddr *) &server, sizeof(server)) < 0)
        error("Socket bind error");

    if (listen(server_fd, BACKLOG) < 0)
        error("Error entering passive mode");

    printf("Server started. Listening on socket '%s'\n", path);

    for (;;) {
        socklen_t client_len = sizeof(client);
        if ((client_fd = accept(server_fd, (struct sockaddr *) &client, &client_len)) < 0)
            error("Error accepting connection");

        printf("Client connected\n");

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