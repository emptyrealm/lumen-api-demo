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
    "filename": "App/Http/Controllers/V1/AuthController.php",
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
          "content": "HTTP/1.1 201 Created\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbHVtZW4tYXBpLWRlbW8uZGV1L2FwaS9hdXRob3JpemF0aW9ucyIsImlhdCI6MTQ4Mzk3NTY5MywiZXhwIjoxNDg5MTU5NjkzLCJuYmYiOjE0ODM5NzU2OTMsImp0aSI6ImViNzAwZDM1MGIxNzM5Y2E5ZjhhNDk4NGMzODcxMWZjIiwic3ViIjo1M30.hdny6T031vVmyWlmnd2aUr4IVM9rm2Wchxg5RX_SDpM\",\n   \"expires_in\": 60,\n   \"refresh_expires_in\": 20160\n}",
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
    "filename": "App/Http/Controllers/V1/AuthController.php",
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
          "content": "{\n  \"Authorization\": \"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY\"\n}",
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
          "content": "HTTP/1.1 200 OK\n{\n   \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbHVtZW4tYXBpLWRlbW8uZGV1L2FwaS9hdXRob3JpemF0aW9ucyIsImlhdCI6MTQ4Mzk3NTY5MywiZXhwIjoxNDg5MTU5NjkzLCJuYmYiOjE0ODM5NzU2OTMsImp0aSI6ImViNzAwZDM1MGIxNzM5Y2E5ZjhhNDk4NGMzODcxMWZjIiwic3ViIjo1M30.hdny6T031vVmyWlmnd2aUr4IVM9rm2Wchxg5RX_SDpM\",\n   \"expires_in\": 60,\n   \"refresh_expires_in\": 20160\n}",
          "type": "json"
        }
      ]
    },
    "filename": "App/Http/Controllers/V1/AuthController.php",
    "groupTitle": "auth",
    "name": "PutV1AuthToken"
  },
  {
    "type": "get",
    "url": "/v1/parts",
    "title": "批量查询型号",
    "group": "parts",
    "version": "1.0.1",
    "permission": [
      {
        "name": "select"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String[]",
            "optional": false,
            "field": "queries",
            "description": "<p>型号列表,json格式,条数限制上限为30条,若超过则全部不解析,并返回对应提示</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "queries.partNo",
            "description": "<p>型号(型号和制造商是需要同时存在的)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "queries.mfr",
            "description": "<p>制造商(型号和制造商是需要同时存在的)</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": true,
            "field": "queries.erpNo",
            "description": "<p>erp物料编号 erp物料编号与[型号和制造商],只需要存在一组即可</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "queries.reference",
            "description": "<p>用来表示当前数据的唯一性,单次请求中必须唯一</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "queries=[{\"partNo\":\"0804MC\",\"mfr\":\"Texas Instruments\",\"erpNo\":\"asdasd\",\"reference\":\"0\"}]",
          "type": "String"
        }
      ]
    },
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>用户的token, value以Bearer 开头</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n  \"Authorization\": \"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String[]",
            "optional": false,
            "field": "parts",
            "description": "<p>型号列表</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.partNo",
            "description": "<p>型号</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.mfrCN",
            "description": "<p>中文制造商</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.mfrEN",
            "description": "<p>英文制造商</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.erpNo",
            "defaultValue": "null",
            "description": "<p>物料编号</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.secondaryCategory",
            "defaultValue": "null",
            "description": "<p>二级分类</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.secondaryCategoryCode",
            "defaultValue": "null",
            "description": "<p>二级分类code</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.descCN",
            "description": "<p>中文描述</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.descEN",
            "description": "<p>英文描述</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.casePackage",
            "defaultValue": "null",
            "description": "<p>封装</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "parts.minimumPackingQty",
            "defaultValue": "null",
            "description": "<p>最小包装</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "parts.lifecycleStatus",
            "defaultValue": "null",
            "description": "<p>生命周期状态 1 在产 2 停产 3 未知(不显示)</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "parts.rohs",
            "defaultValue": "null",
            "description": "<p>rohs  0 1 2 其中0表示不确定，1表示符合，2表示不符合</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "parts.leadFree",
            "defaultValue": "null",
            "description": "<p>leadFree 0 1 2 其中0表示不确定，1表示符合，2表示不符合</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.mainImageUrl",
            "description": "<p>型号主图片url</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.icmallPartId",
            "description": "<p>icmall PartNo ID</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.findicPartId",
            "description": "<p>Findic PartNo ID</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.findicPartUrl",
            "defaultValue": "null",
            "description": "<p>findic网站型号url</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.findicPartPdfUrl",
            "description": "<p>findic网站型号的pdf的url</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "parts.dataUpdateTime",
            "description": "<p>型号数据更新时间(ISO-8601)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n   \"parts\": [\n       {\n           \"partNo\": \"0804MC\",\n           \"mfrCN\": null,\n           \"mfrEN\": \"Texas Instruments\",\n           \"erpNo\": \"testno\",\n           \"secondaryCategory\": null,\n           \"secondaryCategoryCode\": null,\n           \"descCN\": \"8 针 TO-3 插座\",\n           \"descEN\": \"Conn TR Socket SKT 8POS Solder ST Thru-Hole\",\n           \"casePackage\": \"TO-3\",\n           \"minimumPackingQty\": null,\n           \"lifecycleStatus\": 1,\n           \"rohs\": 2,\n           \"leadFree\": 2,\n           \"mainImageUrl\": \"https://pic.ICMall.net/m145/ICMall-0804mc-Product-hURu1fpF-bqV0YavMj.jpg\",\n           \"icmallPartId\": \"563519\",\n           \"findicPartId\": \"4zpaGojQa\",\n           \"findicPartUrl\": null,\n           \"findicPartPdfUrl\": null,\n           \"dataUpdateTime\": \"2018-09-13T07:32:34+00:00\"\n       }\n   ]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "HTTP/1.1 400\n{\n     \"message\": \"Bad Request\",\n     \"status\": 401\n}",
          "type": "json"
        }
      ]
    },
    "filename": "App/Http/Controllers/V1/PartsController.php",
    "groupTitle": "parts",
    "name": "GetV1Parts"
  },
  {
    "type": "patch",
    "url": "/v1/parts/erpNo",
    "title": "更新erpNo",
    "group": "parts",
    "version": "1.0.1",
    "permission": [
      {
        "name": "edit-erpNo"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String[]",
            "optional": false,
            "field": "queries",
            "description": "<p>型号列表,json格式,条数限制上限为30条,若超过则全部不解析,并返回对应提示</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "queries.icmallPartId",
            "description": "<p>icmallPartId</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "queries.erpNo",
            "description": "<p>需要更新的erpNo</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"queries\": [\n\t  \t{\n         \"icmallPartId\": \"511434\",\n         \"erpNo\": \"testno\"\n     },\n\t  \t{\n         \"icmallPartId\": \"511434\",\n         \"erpNo\": \"testno\"\n     }\n ]\n}",
          "type": "json"
        }
      ]
    },
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>用户的token, value以Bearer 开头</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Header-Example:",
          "content": "{\n  \"Authorization\": \"Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "error",
            "description": "<p>items列表中是否有数据沒有更新成功,false为无错误,默认为false,</p>"
          },
          {
            "group": "Success 200",
            "type": "String[]",
            "optional": false,
            "field": "items",
            "description": "<p>型号列表</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "items._icmallPartId",
            "description": "<p>Icmall Id</p>"
          },
          {
            "group": "Success 200",
            "type": "Number",
            "optional": false,
            "field": "items.status",
            "description": "<p>422为更新失败,200为更新成功</p>"
          },
          {
            "group": "Success 200",
            "type": "Object",
            "optional": true,
            "field": "items.error",
            "description": "<p>当status为422时才会有该字段</p>"
          },
          {
            "group": "Success 200",
            "type": "Object",
            "optional": false,
            "field": "items.error.reason",
            "description": "<p>错误原因</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200\n{\n     \"error\": false,\n     \"items\": [\n        {\n         \"_icmallPartId\": \"511434\",\n         \"status\": 422,\n         \"error\": {\n            \"reason\": \"ErpNo 已存在\"\n             }\n        },\n        {\n         \"_icmallPartId\": \"511434\",\n         \"status\": 200\n     ]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "HTTP/1.1 400\n{\n     \"message\": \"Bad Request\",\n     \"status\": 401\n}",
          "type": "json"
        }
      ]
    },
    "filename": "App/Http/Controllers/V1/PartsController.php",
    "groupTitle": "parts",
    "name": "PatchV1PartsErpno"
  }
] });
