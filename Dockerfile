FROM composer:1.10
# image with php7.4

# build docker image with current host permissions (same user id and group id)

ARG USER_ID
ARG GROUP_ID

RUN addgroup -g $GROUP_ID my_group
RUN adduser --disabled-password -g '' -u $USER_ID -G my_group my_user
RUN addgroup my_user

USER my_user