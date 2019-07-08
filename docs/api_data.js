define({ "api": [
  {
    "type": "delete",
    "url": "/v1/auth/token",
    "title": "删除当前token",
    "description": "<p>删除当前token</p>",
    "group": "auth",
    "permission": [
      {
        "name": "jwt"
      }
    ],
    "version": "1.0.1",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 204 No Content",
          "type": "json"
        }
      ]
    },
    "filename": "app/Http/Controllers/V1/AuthController.php",
    "groupTitle": "auth",
    "name": "DeleteV1AuthToken"
  },
  {
    "type": "post",
    "url": "/v1/auth",
    "title": "创建一个token",
    "description": "<p>创建一个token</p>",
    "group": "auth",
    "permission": [
      {
        "name": "none"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Email",
            "optional": false,
            "field": "email",
            "description": "<p>邮箱</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "password",
            "description": "<p>密码 请先用base64将明文加密后传输过来</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "email:your email\npassword:your password",
          "type": "form"
        }
      ]
    },
    "version": "1.0.1",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 201 Created\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sdW1lbi1hcGkubG9jYWxcL3YxXC9hdXRoIiwiaWF0IjoxNTYyNTU3MTkzLCJleHAiOjE1NjI1NjA3OTMsIm5iZiI6MTU2MjU1NzE5MywianRpIjoicmE0MHBNWlE2NUtLd3F4OCIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInNjb3BlcyI6bnVsbH0.yDvkkPMy_BPn1XQIUxpLE476FVVe6p8yTkQ0KmGoMjI\",\n   \"expires_in\": 60,\n   \"refresh_expires_in\": 20160\n}",
          "type": "json"
        }
      ],
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>凭证,用于之后的通信凭证</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "expires_in",
            "description": "<p>当前token有效期</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "refresh_expires_in",
            "description": "<p>token刷新有效期</p>"
          }
        ]
      }
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "HTTP/1.1 401\n{\n  \"error\": \"用户面密码错误\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "app/Http/Controllers/V1/AuthController.php",
    "groupTitle": "auth",
    "name": "PostV1Auth"
  },
  {
    "type": "put",
    "url": "/v1/auth/token",
    "title": "刷新token",
    "description": "<p>刷新token</p>",
    "group": "auth",
    "permission": [
      {
        "name": "jwt"
      }
    ],
    "version": "1.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>用户旧的jwt-token, value以Bearer 开头</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n  \"Authorization\": \"Bearer  eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9pY21hbGxhcGkubG9jYWxcL3YxXC9hdXRoXC90b2tlbiIsImlhdCI6MTU2MTcyMjM5OCwiZXhwIjoxNTYxNzI2MDExLCJuYmYiOjE1NjE3MjI0MTEsImp0aSI6InIzMDc1Mjg0eVdybkNhSnQiLCJzdWIiOjIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjciLCJzY29wZXMiOiJlZGl0LWVycE5vIn0.AfED_DGQVD1bcE9ixsaqdU8BDzho-wZNugByrm5P2gI\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>凭证,用于之后的通信凭证</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "expires_in",
            "description": "<p>当前token有效期</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "refresh_expires_in",
            "description": "<p>token刷新有效期</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sdW1lbi1hcGkubG9jYWxcL3YxXC9hdXRoIiwiaWF0IjoxNTYyNTU3MTkzLCJleHAiOjE1NjI1NjA3OTMsIm5iZiI6MTU2MjU1NzE5MywianRpIjoicmE0MHBNWlE2NUtLd3F4OCIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyIsInNjb3BlcyI6bnVsbH0.yDvkkPMy_BPn1XQIUxpLE476FVVe6p8yTkQ0KmGoMjI\",\n   \"expires_in\": 60,\n   \"refresh_expires_in\": 20160\n}",
          "type": "json"
        }
      ]
    },
    "filename": "app/Http/Controllers/V1/AuthController.php",
    "groupTitle": "auth",
    "name": "PutV1AuthToken"
  }
] });
