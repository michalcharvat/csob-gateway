<?php declare(strict_types = 1);

namespace SlevomatCsobGateway\Call\Masterpass;

use DateTimeImmutable;
use SlevomatCsobGateway\Api\ApiClient;
use SlevomatCsobGateway\Api\Response;
use SlevomatCsobGateway\Api\ResponseCode;
use SlevomatCsobGateway\Call\PaymentStatus;
use SlevomatCsobGateway\Call\ResultCode;

class BasicCheckoutRequestTest extends \PHPUnit\Framework\TestCase
{

	public function testSend(): void
	{
		$apiClient = $this->getMockBuilder(ApiClient::class)
			->disableOriginalConstructor()
			->getMock();

		$apiClient->expects(self::once())->method('post')
			->with('masterpass/basic/checkout', [
				'merchantId' => '012345',
				'payId' => '123456789',
				'callbackUrl' => 'https://www.vasobchod.cz/masterpass/callback',
			])
			->willReturn(
				new Response(ResponseCode::get(ResponseCode::S200_OK), [
					'payId' => '123456789',
					'dttm' => '20140425131559',
					'resultCode' => 0,
					'resultMessage' => 'OK',
					'paymentStatus' => 1,
					'lightboxParams' => [
						'requestToken' => '6a79bf9e320a0460d08aee7ad154f7dab4e19503',
						'callbackUrl' => 'https://www.vasobchod.cz/masterpass/callback',
						'merchantCheckoutId' => 'a4a6w4vzajswviqy5oeu11irc2e3yb51ws',
						'allowedCardTypes' => 'master,visa',
						'suppressShippingAddressEnable' => 'true',
						'loyaltyEnabled' => 'false',
						'version' => 'v6',
						'shippingLocationProfile' => 'SP-0001',
					],
				])
			);

		/** @var ApiClient $apiClient */
		$paymentRequest = new BasicCheckoutRequest(
			'012345',
			'123456789',
			'https://www.vasobchod.cz/masterpass/callback'
		);

		$checkoutResponse = $paymentRequest->send($apiClient);

		$this->assertInstanceOf(CheckoutResponse::class, $checkoutResponse);
		$this->assertSame('123456789', $checkoutResponse->getPayId());
		$this->assertEquals(DateTimeImmutable::createFromFormat('YmdHis', '20140425131559'), $checkoutResponse->getResponseDateTime());
		$this->assertEquals(ResultCode::get(ResultCode::C0_OK), $checkoutResponse->getResultCode());
		$this->assertSame('OK', $checkoutResponse->getResultMessage());
		$this->assertEquals(PaymentStatus::get(PaymentStatus::S1_CREATED), $checkoutResponse->getPaymentStatus());
		$this->assertNotNull($checkoutResponse->getLightboxParams());
	}

}
