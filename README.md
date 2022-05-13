# B2B
A B2B API interface allows regulated third party financial institutions to transact within KAKAUPAY platform, the API can transfer funds from institution collection wallet account to authorized KAKUPAY existing account.

###HTTP REQUIREMENTS
The interface exchange AES-256-CBC encrypted data through SSL with third parties in POST request.

This means we provide an end-to-end encryption when transferring data through HTTP protocol.

Note: When connecting via VPS servers, It is possible to skip the encryption part. to do that consult us development team.

## Forming Http request
These headers are part of every request of the API to form basic authorization of resources and identification of institution

**Base_Url** The remote universal indicate hosting the service	To be provided

| #  | Parameter | Description	  | Value
|---|---|---|---|
|  1 | k-api-key  | The key which identify your business on KAKUPAY  | **To be provided**  |   
|  2 | k-api-id	|The authorization Id which allows communication of resources   | **To be provided**    |   
|  3 | Cookie | A secure cookie which the two way communication uses to perform an action | _Generated by your server or SDK_  |
|  4 | Authorization: Bearer | A temporary key which the application uses to authorize a communication with the server	   | Provided once auth process successfully hit KAKUPAY base_url  |

### Sample http code
``` json
{
    "k-api-key:key",
    "k-api-id:id",
    "Content-Type: text/plain"
}
```

### Response
``` json
{
    "code": "200",
    "message": "Data transfer key generated successfully",
    "feedback": {
    "hash": "bMlIl8tt8N80ojYLRVdDGGJFFVTS8bZUmvpViBrCGB8mSIsO1q+IZX0v5h8US8SY"
    },
    "command": null
}
```
**Authorization;** for the API to work you first request encryption key and Authorization Bearer key from our servers.
1. Decrypt the key using open_ssl algorithm using your private key which we provide to you.
2. Encrypt your payload values using open_ssl algorithm through the decrypted key
3. Use the encrypted key as authorization bearer

##Let get into your first payment

##Disbursement
Use “disbursement” to request funds transfer from collection wallet to KAKAPAY active account

_Endpoint:_ /v1/disbursement
### Sample http code
Encrypt your values if not on VPS
``` json
{
    "amount": "",
    "recipient": "",
    "reference": "",
    "description": ""
}
```
### Response
```json
{
    "code": 200,
    "message": "Payment request received for processing",
    "status": "success",
    "feedback": {
        "reference": "B2B-1652349988",
        "transaction": {
            "amount": "1,000.00 MWK",
            "currency": "MWK",
            "payableAmt": "1,000.00 MWK",
            "reference": "KKB2C.220512.1206.A744",
            "state": "paid",
            "time": 1652349992
        },
        "organization": "NAME OF YOUR INSTITUTION"
    }
}
```

##Checking payment status

##status
Use “status” to request transaction transfer status; This API is useful in a time when an organization needs to know the status of transaction which it recent send us. A transaction can either be _pending, paid, reversed, or failed_

_Endpoint:_ /v1/status
### Sample http code
Encrypt your values if not on VPS
``` json
{
    "reference": ""
}
```
### Response
```json
{
  "code": 200,
  "message": "Payment status query was successful",
  "status": "success",
  "feedback": {
    "reference": "B2B-REF-1",
    "transaction": {
      "amount": "12000.00",
      "currency": "MWK",
      "payableAmt": "12000.00",
      "reference": "KKB2C.220509.1714.V754",
      "state": "paid",
      "currentTime": 1652353405,
      "transStartTime": "2022-05-09 17:14:59",
      "transEndTime": "2022-05-11 23:36:44",
      "narration": "Transaction completed successfully"
    },
    "organization": "NAME OF YOUR INSTITUTION"
  }
}
```


##Retry failed transactions

##retry
Use “retry” to request a failed transaction to be retried

_Endpoint:_ /v1/retry
### Sample http code
Encrypt your values if not on VPS
``` json
{
    "reference": ""
}
```
### Response
```json
{
  "code": 200,
  "message": "Payment request received for processing",
  "status": "error",
  "feedback": {
    "reference": "B2B-REF-1",
    "transaction": {
      "amount": "12000.00",
      "currency": "MWK",
      "payableAmt": "12000.00",
      "reference": "KKB2C.220509.1714.V754",
      "state": "paid",
      "currentTime": 1652354110,
      "transStartTime": "2022-05-09 17:14:59",
      "transEndTime": "2022-05-11 23:36:44",
      "narration": "Transaction scheduled for retry"
    },
    "organization": "NAME OF YOUR INSTITUTION"
  }
}
```


##Checking if provided specimen is an account number on kakupay

##isAccount
Use “IsAccount” to request if the claimed recipient is valid account or in a state to receive payment on KAKUPAY platform

_Endpoint:_ /v1/isAccount
### Sample http code
Encrypt your values if not on VPS
``` json
{
    "recipient": ""
}
```
### Response
```json
{
  "code": 200,
  "message": "Account holder details",
  "status": "success",
  "feedback": {
    "recipient": "099.....",
    "holder": {
      "name": "",
      "status": true,
      "account": "+26599....",
      "authRule": "200",
      "nameWithhold": true
    },
    "organization": "KAKUPAY"
  }
}
```

###`GENERAL INTERFACE STATUS CODES`
|     Code    |            |     Description                                                                                                                                                             |
|-------------|------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
|     1       |     100    |     The request process failed with   general authorization rule or     The request was not succeed   because a required parameter was missing or not properly formatted    |
|     2       |     102    |     Internal application down – you requested a   resource which is not available to you or there is temporary service outrage                                              |
|     3       |     103    |     One of the required parameter for   the service is missing or not properly set                                                                                          |
|     4       |     200    |     The process request was successful, as such   immediate response is in the feedback                                                                                     |
|     5       |     201    |     Transaction failed due to insufficient   balance                                                                                                                        |
|     6       |     202    |     Transaction reference too long (Maximum) 25   characters                                                                                                                |
|     7       |     203    |     Transaction description too long   (Maximum) 20 characters                                                                                                              |
|     8       |     204    |     Transaction recipient is not 4 or 10 digits long                                                                                                                        |
|     9       |     205    |     The merchant account balance is   low – The institution wallet has no balance therefore needs to top-up                                                                 |
|    10       |     500    |     The request process failed because the requested transaction   was not found or the status might have been locked.                                                      |
|    11       |     501    |     Maximum retry period or count exceeded   - Transaction is LOCKED and can no longer processed.                                                                           |
|    12       |     404    |     You do not have privilege to access the requested   resource. Consult the documentation on how to send such a request                                                   |