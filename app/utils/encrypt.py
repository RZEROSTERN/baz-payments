import base64, sys
from cryptography.hazmat.primitives import serialization, hashes
from cryptography.hazmat.primitives.asymmetric import padding

try:
    public_key = serialization.load_pem_public_key(
            f"-----BEGIN PUBLIC KEY-----\n{sys.argv[1]}\n-----END PUBLIC KEY-----".encode(),
    )
    result = base64.b64encode(public_key.encrypt(
            sys.argv[2].encode(encoding="utf8"), padding.OAEP(
                    mgf=padding.MGF1(algorithm=hashes.SHA1()),
                    algorithm=hashes.SHA1(),
                    label=None
            )
    )).decode(encoding="utf8")

    print(result)
except IndexError:
        print("500")