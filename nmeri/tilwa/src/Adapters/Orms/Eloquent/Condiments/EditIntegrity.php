<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Condiments;

	use Tilwa\Adapters\Orms\Eloquent\Models\EditHistory;

	use Illuminate\Database\Eloquent\Relations\Relation;

	/**
	 * Using a trait instead of wrapping model in an additional service, since this is already being returned by a service (MultiUserModelEdit), and would result in clunky DX
	 * 
	 * Using a trait instead of an abstract class since models are likely to require more than one inheritance
	 * 
	 * If classes using this trait want to create history each time a seed is created, they should add this field to their factory:
	 * [edit_history_id => EditHistory::factory()]
	 * 
	 * Otherwise, it should be added at runtime:
	 * EditHistory::factory()->count(x)->for(
	 * 	Product::factory(), "historical"
	 * )->create();
	*/
	trait EditIntegrity {

		const INTEGRITY_COLUMN = "updated_at";

		public function edit_history ():Relation {

			return $this->morphMany(EditHistory::class, "historical");
		}

		public function includesEditIntegrity (string $integrity):bool {

			$primaryField = $this->getKeyName();

			return $this->where([
				
				$primaryField => $this->$primaryField,

				self::INTEGRITY_COLUMN => $integrity
			])->exists();
		}

		public function nullifyEditIntegrity (DateTime $integrity):void {

			$this->update([

				self::INTEGRITY_COLUMN => $integrity
			]);

			if ($this->enableAudit()) $this->makeHistory();
		}

		protected function enableAudit ():bool {

			return true;
		}

		protected function makeHistory ():void {

			$user = $this->authStorage->getUser();

			$this->edit_history()->create([

				"payload" => json_encode($this->payloadStorage->fullPayload()),

				"user_id" => !is_null($user) ? $user->getId(): null
			]);
		}
	}
?>