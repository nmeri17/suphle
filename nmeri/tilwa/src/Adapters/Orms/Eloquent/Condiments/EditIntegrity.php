<?php
	namespace Tilwa\Adapters\Orms\Eloquent\Condiments;

	use Tilwa\Adapters\Orms\Eloquent\Models\ActiveEditors;

	use Illuminate\Database\Eloquent\Relations\Relation;

	/**
	 * Using a trait instead of wrapping model in an additional service, since this is already being returned by a service (MultiUserModelEdit)
	 * 
	 * If using classes want to create viewers each time a seed is created, they should add this field to their factory:
	 * [active_editor_id => ActiveEditors::factory()]
	 * 
	 * Otherwise, it should be added at runtime:
	 * ActiveEditors::factory()->count(x)->for(
	 * 	Product::factory(), "editable"
	 * )->create();
	*/
	trait EditIntegrity {

		const INTEGRITY_COLUMN = "edit_lock";

		public function active_editors ():Relation {

			return $this->morphMany(ActiveEditors::class, "editable");
		}

		public function includesEditIntegrity (int $integrity):bool {

			return $this->whereHas("active_editors", function ($query) use ($integrity) {

				$query->where(self::INTEGRITY_COLUMN, $integrity);
			})->exists();
		}

		public function nullifyEditIntegrity ():void {

			$this->active_editors()->delete();
		}

		public function addEditIntegrity (int $integrity):void {

			$this->active_editors()->create([

				self::INTEGRITY_COLUMN => $integrity
			]);
		}
	}
?>