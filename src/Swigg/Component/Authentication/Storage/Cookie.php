<?php


/**
 * @namespace
 */
namespace Swigg\Component\Authentication\Storage;

use Zend\Authentication\Storage as AuthenticationStorage;

/**
 *
 */
class Cookie implements AuthenticationStorage
{
    const NAME       = 'SW_AUTH';
    const IDENTIFIER = 'ID';
    const DATA       = 'DATA';
    const EXPIRATION = 'EXPIRATION';
    const SEAL       = 'SEAL';

    /**
     * Name used when reading/writing the cookie. Defaults to the class
     * constant NAME.
     *
     * @var string
     */
    protected $name;

    /**
     * Private key used when generating the cookie key. Any string is
     * acceptable, but a string similar to what would be used for
     * a strong password will provide the most security.
     *
     * @var string
     */
    protected $serverKey;

    /**
     * The number of minutes a cookie will be valid after it is written.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Indicates that the cookie should only be transmitted over a secure
     * HTTPS connection from the client. When set to TRUE, the cookie will
     * only be set if a secure connection exists. Defaults to false.
     *
     * @var bool
     */
    protected $isSecure;

    /**
     * Should the cookie be tied to an SSL session. If set to true the cookie
     * will not revalidate after the current SSL session ends. Defaults
     * to false.
     *
     * @var bool
     */
    protected $isLinkedWithSSL;

    /**
     * The domain the cookie is available to. Defaults to the current domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * The path on the server in which the cookie will be available on. Defaults
     * to the current path.
     *
     * @var string
     */
    protected $path;

    /**
     * The adapter used to encrypt cookie data. Currently only Openssl and
     * Mcrypt are supported. If using Openssl the passphrase will be
     * automatically set before being used. If using Mcrypt the vector will
     * automatically be set before being used.
     *
     * @var Zend_Filter_Encrypt_Interface
     */
    protected $encryptAdapter;

    /**
     * The name of the hash algorithm to be used throughout the cookie. Defaults
     * to `sha1`.
     *
     * @var string
     */
    protected $hashAlgorithm;

    /**
     * Constructor
     *
     * @param $serverKey
     * @param array $options
     */
    public function __construct($serverKey, array $options = array())
    {
        $this->setServerKey($serverKey);

        $knownOptions = array(
            'name' => self::NAME,
            'domain' => null,
            'path' => null,
            'isSecure' => false,
            'isLinkedWithSSL' => false,
            'encryptAdapter' => null,
            'hashAlgorithm' => 'sha1'
        );

        $options = array_merge(array_intersect($options, $knownOptions), $knownOptions);

        foreach ($options as $key => $option) {
            $method = 'set' . ucfirst($key);
            call_user_func_array(array($this, $method), $option);
        }
    }

    /**
     * Get the cookie name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Set the cookie name
     *
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        if (!is_scalar($name)) {
            throw new \InvalidArgumentException(sprintf(
                'Property `name` must be a string.'
            ));
        }

        $this->name = $name;
    }

    /**
     * Get the server key
     *
     * @return string
     */
    public function serverKey()
    {
        return $this->serverKey;
    }

    /**
     * Set the server key
     *
     * @param string $serverKey
     */
    public function setServerKey($serverKey)
    {
        if (!is_scalar($serverKey)) {
            throw new \InvalidArgumentException(sprintf(
                'Property `serverKey` must be a string.'
            ));
        }

        $this->serverKey = $serverKey;
    }

    /**
     * Gets the hash algorithm
     *
     * @return string
     */
    public function hashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    /**
     * Sets the hash algorithm
     *
     * @param $hashAlgorithm
     * @throws \InvalidArgumentException
     */
    public function setHashAlgorithm($hashAlgorithm)
    {
        if (!in_array($hashAlgorithm, hash_algos())) {
            throw new \InvalidArgumentException(sprintf(
                'Algorithm `%s` not available on system.', $hashAlgorithm
            ));
        }

        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * Get the cookie lifetime in minutes
     *
     * @return string
     */
    public function lifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set the cookie lifetime in minutes
     *
     * @param int $lifetime
     */
    public function setLifetime($lifetime = null)
    {
        if (!is_int($lifetime) && !is_null($lifetime)) {
            throw new \InvalidArgumentException(sprintf(
                'Property `lifetime` must be an integer or null.'
            ));
        }

        $this->lifetime = $lifetime;
    }

    /**
     * Get the unix timestamp when the cookie will expire
     *
     * @return int
     */
    public function getExpirationTimestamp()
    {
        if (($lifetime = $this->lifetime())) {
            return time() + ($this->lifetime()*60);
        }

        return null;
    }

    /**
     * Get if the cookie requires HTTPS
     *
     * @return bool
     */
    public function isSecure()
    {
        return $this->isSecure;
    }

    /**
     * Set if the cookie requres HTTPS
     *
     * @param bool $isSecureFlag
     */
    public function setIsSecure($isSecureFlag)
    {
        if (!is_bool((bool)$isSecureFlag)) {
            throw new \InvalidArgumentException(sprintf(
                'Property `isSecure` must be a boolean.'
            ));
        }

        $this->isSecure = (bool)$isSecureFlag;
    }

    /**
     * Get the domain
     *
     * @return string
     */
    public function domain()
    {
        return $this->domain;
    }

    /**
     * Set the domain
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        if (!is_scalar((string)$domain)) {
            throw new \InvalidArgumentException(sprintf(
                'Property `domain` must be a string.'
            ));
        }

        $this->domain = $domain;
    }

    /**
     * Get the path
     *
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Set the path
     *
     * @param string $path
     */
    public function setPath($path)
    {
        if (!is_scalar((string)$path)) {
            throw new \InvalidArgumentException(sprintf(
                'Property `path` must be a string.'
            ));
        }

        $this->path = $path;
    }

    /**
     * Get if the cookie is linked to the SSL session
     *
     * @return bool
     */
    public function isLinkedWithSSL()
    {
        return $this->isLinkedWithSSL;
    }

    /**
     * Set if the cookie is linked to the SSL session
     *
     * @param $linkedFlag
     * @throws \InvalidArgumentException
     */
    public function setIsLinkedWithSSL($linkedFlag)
    {
        if (!is_bool((bool)$linkedFlag)) {
            throw new \InvalidArgumentException(sprintf(
                'Property `isLinkedWithSslSession` must be a boolean.'
            ));
        }

        $this->isLinkedWithSSL = (bool)$linkedFlag;
    }


    /**
     * Get the adapter used for encrypting cookie data
     *
     * @return \Zend\Filter\Encrypt
     */
    public function encryptAdapter()
    {
        return $this->encryptAdapter;
    }

    /**
     * Set the adapter used for encrypting cookie data
     *
     * @param null|\Zend\Filter\Encrypt $encryptAdapter
     * @throws \InvalidArgumentException
     */
    public function setEncryptAdapter(\Zend\Filter\Encrypt $encryptAdapter = null)
    {
        if ($encryptAdapter) {
            switch (true) {
                case ($encryptAdapter instanceof \Zend\Filter\Encrypt\Mcrypt):
                    break;
                case ($encryptAdapter instanceof \Zend\Filter\Encrypt\Openssl):
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Only Mcrypt and Openssl are currently supported'
                    ));
            }
        }

        $this->encryptAdapter = $encryptAdapter;
    }

    /**
     * @param string $cookieId
     * @param int $expiration
     * @return void
     */
    protected function prepareEncryptAdapter($cookieId, $expiration)
    {
        if (!($adapter = $this->encryptAdapter())) {
            return;
        }

        $cookieKey = $this->createCookieKey($cookieId, $expiration);

        if ($adapter instanceof \Zend\Filter\Encrypt\Mcrypt) {
            /** @var $adapter  \Zend\Filter\Encrypt\Mcrypt */
            $options = $adapter->getEncryption();

            // determine the settings of the adapter
            $cipher = strtoupper('MCRYPT_' . $options['algorithm']);
            $mode = strtoupper('MCRYPT_MODE_' . $options['mode']);

            // get the initailization vector size
            $ivSize = mcrypt_get_iv_size(constant($cipher), constant($mode));

            // covert the cookie key into a number
            $keyChars = str_split($cookieKey);
            $keyCharValues = array_map('ord', $keyChars);
            $seed = array_sum($keyCharValues);

            // seed the random number generator based off our cookie key
            srand($seed);

            // create the iv
            $vector = mcrypt_create_iv($ivSize, MCRYPT_RAND);

            $adapter->setEncryption(array(
                'key' => $cookieKey,
                'vector' => $vector
            ));
        }

        if ($adapter instanceof \Zend\Filter\Encrypt\Openssl) {
            /** @var $adapter \Zend\Filter\Encrypt\Openssl */
            $adapter->setPassphrase($cookieKey);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        setcookie($this->name());
        unset($_COOKIE[$this->name()]);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return !isset($_COOKIE[$this->name()]);
    }

    /**
     * Determine if the configuration matches the request environment
     *
     * @throws \Exception if HTTP protocol but requires HTTPS or linked with SSL session id
     * @return boolean
     */
    protected function checkConfiguration()
    {
        if ($this->isSecure() && !isset($_SERVER['HTTPS'])) {
            throw new \Exception(sprintf(
                '`isSecure` set to true but protocol is not HTTPS'
            ));
        }

        if ($this->isLinkedWithSSL() && !($id = $this->getSslSessionId())) {
            throw new \Exception(sprintf(
                '`isLinkedWithSslSession` set to true but no ssl session id found'
            ));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $this->checkConfiguration();

        if ($this->isEmpty()) {
            return false;
        }

        $rawCookie = $_COOKIE[$this->name()];

        $filter = new \Zend\Filter\Decompress(array(
            'adapter'=>'Gz',
            'options' => array(
                'level' => 9
            )
        ));
        $rawCookie = $filter->filter($rawCookie);

        $cookie = unserialize($rawCookie);

        $this->prepareEncryptAdapter($cookie[self::IDENTIFIER], $cookie[self::EXPIRATION]);

        if ($this->verifySeal($cookie)) {
            return $this->prepareForRead($cookie[self::DATA]);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($contents)
    {
        $this->checkConfiguration();

        $expiration = $this->getExpirationTimestamp();
        // cookieId provides uniqueness across all cookie instances to prevent volume attacks
        $cookieId = uniqid();

        $this->prepareEncryptAdapter($cookieId, $expiration);
        $cookie = $this->createCookie($cookieId, $expiration, $contents);

        $rawCookie = serialize($cookie);

        $filter = new \Zend\Filter\Compress(array(
            'adapter'=>'Gz',
            'options' => array(
                'level' => 9
            )
        ));
        $rawCookie = $filter->filter($rawCookie);

        $_COOKIE[$this->name()] = $rawCookie;
        setCookie(
            $this->name(),   // name
            $rawCookie,         // data
            $expiration,        // expiration
            $this->path(),   // path
            $this->domain(), // domain
            $this->isSecure()   // is https only
        );
    }

    /**
     * Creates the array representation of the cookie
     *
     * @param string $cookieId
     * @param int $expiration
     * @param mixed $data
     * @return array
     */
    protected function createCookie($cookieId, $expiration, $data)
    {
        return array(
            self::IDENTIFIER => $cookieId,
            self::EXPIRATION => $expiration,
            self::DATA => $this->prepareForWrite($data),
            self::SEAL => $this->createSeal($cookieId, $expiration, $data)
        );
    }

    /**
     * Returns the serialized data. If encrypt adapter is set on the class then
     * the data is also encrypted.
     *
     * @param mixed $data
     * @return string
     */
    protected function prepareForWrite($data)
    {
        $data = serialize($data);

        if (($adapter = $this->encryptAdapter())) {
            $data = $adapter->encrypt($data);
        }

        return $data;
    }

    /**
     * Returns the unserialied data. If encrypt adapter is set on the class then
     * the data is also decrypted.
     *
     * @param string $data
     * @return mixed
     */
    protected function prepareForRead($data)
    {
        if (($adapter = $this->encryptAdapter())) {
            $data = $adapter->decrypt($data);
        }

        // if $data can not be unserialized, just return false
        // no notices need to be thrown
        return @unserialize($data);
    }

    /**
     * Creates the seal used to detect tampering. If isLinkedWithSslSession
     * the seal will be created using the session id to prevent replay attacks.
     *
     * @param string $cookieId
     * @param int $expiration
     * @param mixed $data
     * @return string
     */
    protected function createSeal($cookieId, $expiration, $data)
    {
        $sealData = array(
            $cookieId,
            $expiration,
            $data
        );

        if ($this->isLinkedWithSSL()) {
            array_push($sealData, $this->getSslSessionId());
        }

        return hash_hmac(
            $this->hashAlgorithm(), serialize($sealData), $this->createCookieKey($cookieId, $expiration)
        );
    }

    /**
     * Checks if the contents of the cookie are valid
     *
     * @param string $cookie
     * @return bool
     */
    protected function verifySeal($cookie)
    {
        $expiration = $cookie[self::EXPIRATION];
        $cookieId = $cookie[self::IDENTIFIER];

        if (is_int($expiration) && $expiration < time()) {
            return false;
        }

        if (($data = $this->prepareForRead($cookie[self::DATA]))) {
            if ($cookie == $this->createCookie($cookieId, $expiration, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create cookie key.
     *
     * @param string $cookieId
     * @param int $expiration
     * @return string
     */
    protected function createCookieKey($cookieId, $expiration)
    {
        return hash_hmac($this->hashAlgorithm(), serialize(array(
            $cookieId,
            $expiration
        )), $this->serverKey());
    }

    /**
     * Get the session id of the HTTPS session. If no session id is found
     * then this method will return false.
     *
     * @return string|false
     */
    protected function getSslSessionId()
    {
        $sessionId = false;

        if (isset($_SERVER['SSL_SESSION_ID'])) {
            $sessionId = $_SERVER['SSL_SESSION_ID'];
        }

        return $sessionId;
    }
}