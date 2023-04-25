<?php

namespace Suphle\Auth\Storage;

use Suphle\Contracts\{Config\AuthContract, IO\EnvAccessor};

use Suphle\Request\PayloadStorage;

use Firebase\JWT\{JWT, Key};

use Throwable;

class TokenStorage extends BaseAuthStorage
{
    final public const AUTHORIZATION_HEADER = "Authorization";

    private const IDENTIFIER_KEY = "user_id",

    ENCODING_ALGO = "HS256";

    public function __construct(protected readonly EnvAccessor $envAccessor, protected readonly PayloadStorage $payloadStorage)
    {
    }

    /**
     * {@inheritdoc}
    */
    public function resumeSession(): void
    {

        if (!$this->payloadStorage->hasHeader(self::AUTHORIZATION_HEADER)) {

            return;
        }

        try {

            $incomingToken = explode(
                " ",
                $this->payloadStorage->getHeaderLine(self::AUTHORIZATION_HEADER)
            )[1]; // the bearer part

            $decoded = JWT::decode(
                $incomingToken,
                new Key(
                    $this->envAccessor->getField("APP_SECRET_KEY"),
                    self::ENCODING_ALGO
                )
            );
        } catch (Throwable $exception) {

            var_dump(
                "Unable to decode token",
                $exception->getMessage(),
                $exception::class
            );

            return;
        }

        $this->identifier = $decoded->data->{self::IDENTIFIER_KEY};
    }

    /**
     * {@inheritdoc}
    */
    public function startSession(string $value): string
    {

        $issuedAt = time();

        $envAccessor = $this->envAccessor;

        $tokenDetails = [
            "iss" => $envAccessor->getField("SITE_HOST"),
            // "aud" => $audience, // $audience
            "iat" => $issuedAt,

            //"nbf" => $issuedAt + 10, // in seconds

            "exp" => $issuedAt + $envAccessor->getField("JWT_TTL"),

            "data" => [self::IDENTIFIER_KEY => $value]
        ];

        $outgoingToken = JWT::encode(
            $tokenDetails,
            $envAccessor->getField("APP_SECRET_KEY"),
            self::ENCODING_ALGO
        );

        $this->identifier = $value; // manually trigger resumption. Can't use getId/resumeSession since it expects to read payload, which isn't valid reaction for this mechanism

        return $outgoingToken;
    }
}
