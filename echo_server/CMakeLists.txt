cmake_minimum_required(VERSION 3.12)
project(echo_server C)

set(CMAKE_C_STANDARD 99)

add_executable(tcp_server tcp_server.c)
add_executable(unix_socket unix_socket.c)